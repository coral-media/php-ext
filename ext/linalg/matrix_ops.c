#include "../lapack_bridge.h"
#include "../linalg_internal.h"

#ifdef USE_SYSTEM_LAPACK
    #include <cblas.h>
#else
    #error "System OpenBLAS required"
#endif

#include <math.h>
#include <string.h>

/* ---------- SVD ---------- */

void linear_algebra_svd_zval(
    zval *x,
    int rows,
    int cols,
    zval *jobz_zv,
    zval *return_value
) {
    if (Z_TYPE_P(x) != IS_ARRAY) {
        zend_type_error("SVD expects array input");
        return;
    }

    char jobz = svd_jobz_from_zval(jobz_zv);
    if (jobz == 0) return;

    int m = rows;
    int n = cols;
    int k = (m < n) ? m : n;

    /* LAPACK column-major */
    int lda = m;

    int info = 0;
    int ldu = 1;
    int ldvt = 1;

    if (m <= 0 || n <= 0) {
        zend_value_error("SVD: rows and cols must be > 0");
        return;
    }

    if (zend_hash_num_elements(Z_ARRVAL_P(x)) != (uint32_t)(m * n)) {
        zend_value_error("SVD: array size must be rows * cols");
        return;
    }

    /* Optional outputs */
    float *U  = NULL;
    float *VT = NULL;

    if (jobz == LA_SVD_REDUCED) {
        ldu  = m;
        ldvt = k;
        U  = emalloc(sizeof(float) * m * k);
        VT = emalloc(sizeof(float) * k * n);
    }
    else if (jobz == LA_SVD_FULL) {
        ldu  = m;
        ldvt = n;
        U  = emalloc(sizeof(float) * m * m);
        VT = emalloc(sizeof(float) * n * n);
    }
    else { /* 'N' */
        ldu = ldvt = 1; /* not referenced by LAPACK when jobz='N' */
    }

    /* Matrix A (column-major) */
    float *A = emalloc(sizeof(float) * m * n);
    fill_matrix_col_major(x, A, m, n);

    /* Singular values */
    float *S = emalloc(sizeof(float) * k);

    /* Workspace query */
    float *work = NULL;
    int lwork = -1;
    float wkopt = 0.0f;
    int *iwork = emalloc(sizeof(int) * (8 * k));

    sgesdd_(
        &jobz, &m, &n,
        A, &lda,
        S,
        U, &ldu,
        VT, &ldvt,
        &wkopt, &lwork,
        iwork, &info
    );

    if (info != 0) {
        zend_error(E_ERROR, "SVD workspace query failed (info=%d)", info);
        goto cleanup;
    }

    lwork = (int) wkopt;
    if (lwork < 1) {
        zend_error(E_ERROR, "SVD workspace query returned invalid lwork=%d", lwork);
        goto cleanup;
    }

    work = emalloc(sizeof(float) * lwork);

    /* Compute */
    sgesdd_(
        &jobz, &m, &n,
        A, &lda,
        S,
        U, &ldu,
        VT, &ldvt,
        work, &lwork,
        iwork, &info
    );

    if (info != 0) {
        zend_error(E_ERROR, "SVD failed (info=%d)", info);
        goto cleanup;
    }

    /* Return values */
    if (jobz == 'N') {
        array_init_size(return_value, k);
        for (int i = 0; i < k; i++) {
            add_next_index_double(return_value, (double) S[i]);
        }
    } else {
        array_init(return_value);

        zval zU, zS, zVT;
        array_init(&zU);
        array_init(&zS);
        array_init(&zVT);

        int u_size  = (jobz == 'A') ? (m * m) : (m * k);
        int vt_size = (jobz == 'A') ? (n * n) : (k * n);

        for (int i = 0; i < u_size; i++) {
            add_next_index_double(&zU, (double) U[i]);
        }
        for (int i = 0; i < k; i++) {
            add_next_index_double(&zS, (double) S[i]);
        }
        for (int i = 0; i < vt_size; i++) {
            add_next_index_double(&zVT, (double) VT[i]);
        }

        add_assoc_zval(return_value, "U",  &zU);
        add_assoc_zval(return_value, "S",  &zS);
        add_assoc_zval(return_value, "Vt", &zVT);
    }

cleanup:
    if (A) efree(A);
    if (S) efree(S);
    if (iwork) efree(iwork);
    if (U) efree(U);
    if (VT) efree(VT);
    if (work) efree(work);
}

/* ---------- MATRIX MULTIPLICATION ---------- */

void linear_algebra_matmul_zval(
    zval *a,
    zval *b,
    int m,
    int n,
    int k,
    zend_bool transpose_a,
    zend_bool transpose_b,
    zval *return_value
) {
    if (Z_TYPE_P(a) != IS_ARRAY || Z_TYPE_P(b) != IS_ARRAY) {
        zend_type_error("matmul(a, b) expects two arrays");
        return;
    }

    HashTable *ha = Z_ARRVAL_P(a);
    HashTable *hb = Z_ARRVAL_P(b);

    // Determine expected sizes based on transpose flags
    int a_expected = transpose_a ? (n * m) : (m * n);
    int b_expected = transpose_b ? (k * n) : (n * k);

    int a_size = zend_hash_num_elements(ha);
    int b_size = zend_hash_num_elements(hb);

    if (a_size != a_expected) {
        zend_value_error("matmul(): matrix A size mismatch (expected %d, got %d)", a_expected, a_size);
        return;
    }

    if (b_size != b_expected) {
        zend_value_error("matmul(): matrix B size mismatch (expected %d, got %d)", b_expected, b_size);
        return;
    }

    // Allocate matrices
    float *ma = emalloc(sizeof(float) * a_size);
    float *mb = emalloc(sizeof(float) * b_size);
    float *mc = emalloc(sizeof(float) * m * k);

    // Fill matrix A (convert from row-major to column-major for BLAS)
    fill_matrix_col_major(a, ma, transpose_a ? n : m, transpose_a ? m : n);

    // Fill matrix B (convert from row-major to column-major for BLAS)
    fill_matrix_col_major(b, mb, transpose_b ? k : n, transpose_b ? n : k);

    /*
     * cblas_sgemm performs: C = alpha * op(A) * op(B) + beta * C
     *
     * Parameters:
     * - Order: CblasColMajor (matrices stored column-major)
     * - TransA/TransB: CblasNoTrans or CblasTrans
     * - M: number of rows in op(A) and C
     * - N: number of columns in op(B) and C
     * - K: number of columns in op(A) and rows in op(B)
     * - alpha: scalar multiplier for A*B
     * - A: matrix A
     * - lda: leading dimension of A (stride)
     * - B: matrix B
     * - ldb: leading dimension of B
     * - beta: scalar multiplier for C (0.0 since we don't add to existing C)
     * - C: result matrix
     * - ldc: leading dimension of C
     */

    CBLAS_TRANSPOSE trans_a = transpose_a ? CblasTrans : CblasNoTrans;
    CBLAS_TRANSPOSE trans_b = transpose_b ? CblasTrans : CblasNoTrans;

    int lda = transpose_a ? n : m;
    int ldb = transpose_b ? k : n;

    cblas_sgemm(
        CblasColMajor,
        trans_a,
        trans_b,
        m,      // rows in result
        k,      // cols in result
        n,      // shared dimension
        1.0f,   // alpha
        ma, lda,
        mb, ldb,
        0.0f,   // beta
        mc, m   // ldc
    );

    // Convert result from column-major back to row-major for PHP
    array_init_size(return_value, m * k);

    for (int row = 0; row < m; row++) {
        for (int col = 0; col < k; col++) {
            // Column-major: C[col*m + row]
            // Row-major index: row*k + col
            add_next_index_double(return_value, (double)mc[col * m + row]);
        }
    }

    efree(ma);
    efree(mb);
    efree(mc);
}

/* ---------- ELEMENT-WISE OPERATIONS ---------- */

void linear_algebra_matrix_add_zval(
    zval *a,
    zval *b,
    int rows,
    int cols,
    zval *return_value
) {
    if (Z_TYPE_P(a) != IS_ARRAY || Z_TYPE_P(b) != IS_ARRAY) {
        zend_type_error("matrixAdd(a, b) expects two arrays");
        return;
    }

    HashTable *ha = Z_ARRVAL_P(a);
    HashTable *hb = Z_ARRVAL_P(b);

    int size = rows * cols;
    int a_size = zend_hash_num_elements(ha);
    int b_size = zend_hash_num_elements(hb);

    if (a_size != size) {
        zend_value_error("matrixAdd(): matrix A size mismatch (expected %d, got %d)", size, a_size);
        return;
    }

    if (b_size != size) {
        zend_value_error("matrixAdd(): matrix B size mismatch (expected %d, got %d)", size, b_size);
        return;
    }

    if (size == 0) {
        zend_value_error("matrixAdd(): matrices must not be empty");
        return;
    }

    float *fa = emalloc(sizeof(float) * size);
    float *fb = emalloc(sizeof(float) * size);

    int i = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(ha, val) {
        fa[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    i = 0;
    ZEND_HASH_FOREACH_VAL(hb, val) {
        fb[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    array_init_size(return_value, size);
    for (i = 0; i < size; i++) {
        add_next_index_double(return_value, (double)(fa[i] + fb[i]));
    }

    efree(fa);
    efree(fb);
}

void linear_algebra_matrix_subtract_zval(
    zval *a,
    zval *b,
    int rows,
    int cols,
    zval *return_value
) {
    if (Z_TYPE_P(a) != IS_ARRAY || Z_TYPE_P(b) != IS_ARRAY) {
        zend_type_error("matrixSubtract(a, b) expects two arrays");
        return;
    }

    HashTable *ha = Z_ARRVAL_P(a);
    HashTable *hb = Z_ARRVAL_P(b);

    int size = rows * cols;
    int a_size = zend_hash_num_elements(ha);
    int b_size = zend_hash_num_elements(hb);

    if (a_size != size) {
        zend_value_error("matrixSubtract(): matrix A size mismatch (expected %d, got %d)", size, a_size);
        return;
    }

    if (b_size != size) {
        zend_value_error("matrixSubtract(): matrix B size mismatch (expected %d, got %d)", size, b_size);
        return;
    }

    if (size == 0) {
        zend_value_error("matrixSubtract(): matrices must not be empty");
        return;
    }

    float *fa = emalloc(sizeof(float) * size);
    float *fb = emalloc(sizeof(float) * size);

    int i = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(ha, val) {
        fa[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    i = 0;
    ZEND_HASH_FOREACH_VAL(hb, val) {
        fb[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    array_init_size(return_value, size);
    for (i = 0; i < size; i++) {
        add_next_index_double(return_value, (double)(fa[i] - fb[i]));
    }

    efree(fa);
    efree(fb);
}

void linear_algebra_matrix_hadamard_zval(
    zval *a,
    zval *b,
    int rows,
    int cols,
    zval *return_value
) {
    if (Z_TYPE_P(a) != IS_ARRAY || Z_TYPE_P(b) != IS_ARRAY) {
        zend_type_error("matrixHadamard(a, b) expects two arrays");
        return;
    }

    HashTable *ha = Z_ARRVAL_P(a);
    HashTable *hb = Z_ARRVAL_P(b);

    int size = rows * cols;
    int a_size = zend_hash_num_elements(ha);
    int b_size = zend_hash_num_elements(hb);

    if (a_size != size) {
        zend_value_error("matrixHadamard(): matrix A size mismatch (expected %d, got %d)", size, a_size);
        return;
    }

    if (b_size != size) {
        zend_value_error("matrixHadamard(): matrix B size mismatch (expected %d, got %d)", size, b_size);
        return;
    }

    if (size == 0) {
        zend_value_error("matrixHadamard(): matrices must not be empty");
        return;
    }

    float *fa = emalloc(sizeof(float) * size);
    float *fb = emalloc(sizeof(float) * size);

    int i = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(ha, val) {
        fa[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    i = 0;
    ZEND_HASH_FOREACH_VAL(hb, val) {
        fb[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    array_init_size(return_value, size);
    for (i = 0; i < size; i++) {
        add_next_index_double(return_value, (double)(fa[i] * fb[i]));
    }

    efree(fa);
    efree(fb);
}

void linear_algebra_matrix_divide_zval(
    zval *a,
    zval *b,
    int rows,
    int cols,
    zval *return_value
) {
    if (Z_TYPE_P(a) != IS_ARRAY || Z_TYPE_P(b) != IS_ARRAY) {
        zend_type_error("matrixDivide(a, b) expects two arrays");
        return;
    }

    HashTable *ha = Z_ARRVAL_P(a);
    HashTable *hb = Z_ARRVAL_P(b);

    int size = rows * cols;
    int a_size = zend_hash_num_elements(ha);
    int b_size = zend_hash_num_elements(hb);

    if (a_size != size) {
        zend_value_error("matrixDivide(): matrix A size mismatch (expected %d, got %d)", size, a_size);
        return;
    }

    if (b_size != size) {
        zend_value_error("matrixDivide(): matrix B size mismatch (expected %d, got %d)", size, b_size);
        return;
    }

    if (size == 0) {
        zend_value_error("matrixDivide(): matrices must not be empty");
        return;
    }

    float *fa = emalloc(sizeof(float) * size);
    float *fb = emalloc(sizeof(float) * size);

    int i = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(ha, val) {
        fa[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    i = 0;
    ZEND_HASH_FOREACH_VAL(hb, val) {
        fb[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    array_init_size(return_value, size);
    for (i = 0; i < size; i++) {
        if (fb[i] == 0.0f) {
            efree(fa);
            efree(fb);
            zend_value_error("matrixDivide(): division by zero at element %d", i);
            return;
        }
        add_next_index_double(return_value, (double)(fa[i] / fb[i]));
    }

    efree(fa);
    efree(fb);
}

/* ---------- SCALAR OPERATIONS ---------- */

void linear_algebra_matrix_scale_zval(
    zval *a,
    double scalar,
    int rows,
    int cols,
    zval *return_value
) {
    if (Z_TYPE_P(a) != IS_ARRAY) {
        zend_type_error("matrixScale(a, scalar) expects an array");
        return;
    }

    HashTable *ha = Z_ARRVAL_P(a);
    int size = rows * cols;
    int a_size = zend_hash_num_elements(ha);

    if (a_size != size) {
        zend_value_error("matrixScale(): matrix size mismatch (expected %d, got %d)", size, a_size);
        return;
    }

    if (size == 0) {
        zend_value_error("matrixScale(): matrix must not be empty");
        return;
    }

    float *fa = emalloc(sizeof(float) * size);

    int i = 0;
    zval *val;
    ZEND_HASH_FOREACH_VAL(ha, val) {
        fa[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    float scalar_f = (float) scalar;

    array_init_size(return_value, size);
    for (i = 0; i < size; i++) {
        add_next_index_double(return_value, (double)(fa[i] * scalar_f));
    }

    efree(fa);
}

void linear_algebra_matrix_add_scalar_zval(
    zval *a,
    double scalar,
    int rows,
    int cols,
    zval *return_value
) {
    if (Z_TYPE_P(a) != IS_ARRAY) {
        zend_type_error("matrixAddScalar(a, scalar) expects an array");
        return;
    }

    HashTable *ha = Z_ARRVAL_P(a);
    int size = rows * cols;
    int a_size = zend_hash_num_elements(ha);

    if (a_size != size) {
        zend_value_error("matrixAddScalar(): matrix size mismatch (expected %d, got %d)", size, a_size);
        return;
    }

    if (size == 0) {
        zend_value_error("matrixAddScalar(): matrix must not be empty");
        return;
    }

    float *fa = emalloc(sizeof(float) * size);

    int i = 0;
    zval *val;
    ZEND_HASH_FOREACH_VAL(ha, val) {
        fa[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    float scalar_f = (float) scalar;

    array_init_size(return_value, size);
    for (i = 0; i < size; i++) {
        add_next_index_double(return_value, (double)(fa[i] + scalar_f));
    }

    efree(fa);
}

void linear_algebra_matrix_multiply_scalar_zval(
    zval *a,
    double scalar,
    int rows,
    int cols,
    zval *return_value
) {
    if (Z_TYPE_P(a) != IS_ARRAY) {
        zend_type_error("matrixMultiplyScalar(a, scalar) expects an array");
        return;
    }

    HashTable *ha = Z_ARRVAL_P(a);
    int size = rows * cols;
    int a_size = zend_hash_num_elements(ha);

    if (a_size != size) {
        zend_value_error("matrixMultiplyScalar(): matrix size mismatch (expected %d, got %d)", size, a_size);
        return;
    }

    if (size == 0) {
        zend_value_error("matrixMultiplyScalar(): matrix must not be empty");
        return;
    }

    float *fa = emalloc(sizeof(float) * size);

    int i = 0;
    zval *val;
    ZEND_HASH_FOREACH_VAL(ha, val) {
        fa[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    float scalar_f = (float) scalar;

    array_init_size(return_value, size);
    for (i = 0; i < size; i++) {
        add_next_index_double(return_value, (double)(fa[i] * scalar_f));
    }

    efree(fa);
}

void linear_algebra_matrix_divide_scalar_zval(
    zval *a,
    double scalar,
    int rows,
    int cols,
    zval *return_value
) {
    if (Z_TYPE_P(a) != IS_ARRAY) {
        zend_type_error("matrixDivideScalar(a, scalar) expects an array");
        return;
    }

    if (scalar == 0.0) {
        zend_value_error("matrixDivideScalar(): division by zero");
        return;
    }

    HashTable *ha = Z_ARRVAL_P(a);
    int size = rows * cols;
    int a_size = zend_hash_num_elements(ha);

    if (a_size != size) {
        zend_value_error("matrixDivideScalar(): matrix size mismatch (expected %d, got %d)", size, a_size);
        return;
    }

    if (size == 0) {
        zend_value_error("matrixDivideScalar(): matrix must not be empty");
        return;
    }

    float *fa = emalloc(sizeof(float) * size);

    int i = 0;
    zval *val;
    ZEND_HASH_FOREACH_VAL(ha, val) {
        fa[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    float scalar_f = (float) scalar;

    array_init_size(return_value, size);
    for (i = 0; i < size; i++) {
        add_next_index_double(return_value, (double)(fa[i] / scalar_f));
    }

    efree(fa);
}
