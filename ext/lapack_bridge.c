#include "lapack_bridge.h"
#ifdef USE_SYSTEM_LAPACK
#include <cblas.h>
#else
#error "System OpenBLAS required"
#endif

extern void sgesdd_(
    char *jobz,
    int *m,
    int *n,
    float *a,
    int *lda,
    float *s,
    float *u,
    int *ldu,
    float *vt,
    int *ldvt,
    float *work,
    int *lwork,
    int *iwork,
    int *info
);

void fill_matrix_col_major(zval *arr, float *A, int m, int n)
{
    int i = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(Z_ARRVAL_P(arr), val) {
        int row = i / n;
        int col = i % n;
        A[col * m + row] = (float) zval_get_double(val);
        i++;
    } ZEND_HASH_FOREACH_END();
}

void fill_float_array_from_php_array(zval *arr, float *out, size_t n)
{
    size_t i = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(Z_ARRVAL_P(arr), val) {
        if (i >= n) break;
        out[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();
}

/* (wrapper stays exactly the same) */
double linear_algebra_dot_zval(zval *a, zval *b)
{
    if (Z_TYPE_P(a) != IS_ARRAY || Z_TYPE_P(b) != IS_ARRAY) {
        zend_type_error("dot(a, b) expects two arrays");
        return 0.0;
    }

    HashTable *ht_a = Z_ARRVAL_P(a);
    HashTable *ht_b = Z_ARRVAL_P(b);

    int n = (int) zend_hash_num_elements(ht_a);
    if (n == 0 || n != (int) zend_hash_num_elements(ht_b)) {
        zend_value_error("Vectors must be non-empty and same length");
        return 0.0;
    }

    float *va = emalloc(sizeof(float) * n);
    float *vb = emalloc(sizeof(float) * n);

    int i = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(ht_a, val) { va[i++] = (float) zval_get_double(val); } ZEND_HASH_FOREACH_END();
    i = 0;
    ZEND_HASH_FOREACH_VAL(ht_b, val) { vb[i++] = (float) zval_get_double(val); } ZEND_HASH_FOREACH_END();

    float result = cblas_sdot(n, va, 1, vb, 1);

    efree(va);
    efree(vb);

    return (double) result;
}

double linear_algebra_norm_zval(zval *x, int method)
{
    if (Z_TYPE_P(x) != IS_ARRAY) {
        zend_type_error("norm(x, method) expects x to be an array");
        return 0.0;
    }

    HashTable *ht = Z_ARRVAL_P(x);
    int n = (int) zend_hash_num_elements(ht);

    if (n == 0) {
        zend_value_error("norm(x, method): array must not be empty");
        return 0.0;
    }

    float *vx = emalloc(sizeof(float) * n);

    int i = 0;
    zval *val;
    ZEND_HASH_FOREACH_VAL(ht, val) {
        vx[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    float result;

    switch (method) {
        case 0: /* L1 */
            result = cblas_sasum(n, vx, 1);
            break;

        case 1: /* L2 */
            result = cblas_snrm2(n, vx, 1);
            break;

        case 2: /* L-infinity */
        {
            int idx = cblas_isamax(n, vx, 1);
            result = fabsf(vx[idx]);
            break;
        }

        default:
            efree(vx);
            zend_value_error("norm(x, method): invalid method");
            return 0.0;
    }

    efree(vx);
    return (double) result;
}

void linear_algebra_svd_zval(
    zval *x,
    int rows,
    int cols,
    char jobz,
    zval *return_value
) {
    int m = rows;
    int n = cols;
    int k = (m < n) ? m : n;
    int lda = m;          /* column-major */
    int ldu, ldvt;
    int info;

    if (Z_TYPE_P(x) != IS_ARRAY) {
        zend_type_error("SVD expects array input");
        return;
    }

    if (zend_hash_num_elements(Z_ARRVAL_P(x)) != (uint32_t)(m * n)) {
        zend_value_error("SVD: array size must be rows * cols");
        return;
    }

    /* ---- JOBZ handling ---- */
    float *U = NULL;
    float *VT = NULL;

    if (jobz == 'S') {
        ldu  = m;
        ldvt = k;
        U  = emalloc(sizeof(float) * m * k);
        VT = emalloc(sizeof(float) * k * n);
    }
    else if (jobz == 'A') {
        ldu  = m;
        ldvt = n;
        U  = emalloc(sizeof(float) * m * m);
        VT = emalloc(sizeof(float) * n * n);
    }
    else if (jobz == 'N') {
        ldu = ldvt = 1; /* not referenced */
    }
    else {
        zend_value_error("SVD: invalid jobz (use 'N', 'S', or 'A')");
        return;
    }

    /* ---- Allocate matrix A (column-major) ---- */
    float *A = emalloc(sizeof(float) * m * n);
    memset(A, 0, sizeof(float) * m * n);

    /* PHP array is row-major → convert */
    zval *val;
    int idx = 0;
    ZEND_HASH_FOREACH_VAL(Z_ARRVAL_P(x), val) {
        int row = idx / n;
        int col = idx % n;
        A[col * m + row] = (float) zval_get_double(val);
        idx++;
    } ZEND_HASH_FOREACH_END();

    /* ---- Singular values ---- */
    float *S = emalloc(sizeof(float) * k);

    /* ---- Workspace ---- */
    int lwork = -1;
    float wkopt;
    int *iwork = emalloc(sizeof(int) * (8 * k));

    /* ---- Workspace query ---- */
    sgesdd_(
        &jobz,
        &m,
        &n,
        A,
        &lda,
        S,
        U,
        &ldu,
        VT,
        &ldvt,
        &wkopt,
        &lwork,
        iwork,
        &info
    );

    if (info != 0) {
        zend_error(E_ERROR, "SVD workspace query failed (info=%d)", info);
        goto cleanup;
    }

    lwork = (int) wkopt;
    float *work = emalloc(sizeof(float) * lwork);

    /* ---- Compute SVD ---- */
    sgesdd_(
        &jobz,
        &m,
        &n,
        A,
        &lda,
        S,
        U,
        &ldu,
        VT,
        &ldvt,
        work,
        &lwork,
        iwork,
        &info
    );

    if (info != 0) {
        zend_error(E_ERROR, "SVD failed (info=%d)", info);
        goto cleanup;
    }

    /* ---- Return values ---- */
    if (jobz == 'N') {
        array_init_size(return_value, k);
        for (int i = 0; i < k; i++) {
            add_next_index_double(return_value, S[i]);
        }
    } else {
        array_init(return_value);

        zval zU, zS, zVT;
        array_init(&zU);
        array_init(&zS);
        array_init(&zVT);

        int u_size  = (jobz == 'A') ? m * m : m * k;
        int vt_size = (jobz == 'A') ? n * n : k * n;

        for (int i = 0; i < u_size; i++) {
            add_next_index_double(&zU, U[i]);
        }

        for (int i = 0; i < k; i++) {
            add_next_index_double(&zS, S[i]);
        }

        for (int i = 0; i < vt_size; i++) {
            add_next_index_double(&zVT, VT[i]);
        }

        add_assoc_zval(return_value, "U",  &zU);
        add_assoc_zval(return_value, "S",  &zS);
        add_assoc_zval(return_value, "Vt", &zVT);
    }

cleanup:
    efree(A);
    efree(S);
    efree(iwork);
    if (U) efree(U);
    if (VT) efree(VT);
    if (lwork > 0) efree(work);
}