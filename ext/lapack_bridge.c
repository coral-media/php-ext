#include "lapack_bridge.h"

#ifdef USE_SYSTEM_LAPACK
    #include <cblas.h>
#else
    #error "System OpenBLAS required"
#endif

#include <math.h>
#include <string.h>

#define LA_NORM_L1   0
#define LA_NORM_L2   1
#define LA_NORM_LINF 2

/* LAPACK SGESDD (Fortran symbol) */
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

/* ---------- Helpers ---------- */

void fill_float_array_from_php_array(zval *arr, float *out, size_t n)
{
    size_t i = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(Z_ARRVAL_P(arr), val) {
        if (i >= n) break;
        out[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();
}

/* PHP input is row-major: [a11,a12,...,a1n, a21,...] */
/* LAPACK expects column-major: A[col*m + row] */
void fill_matrix_col_major(zval *arr, float *A, int m, int n)
{
    int idx = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(Z_ARRVAL_P(arr), val) {
        int row = idx / n;
        int col = idx % n;
        A[col * m + row] = (float) zval_get_double(val);
        idx++;
    } ZEND_HASH_FOREACH_END();
}

static char svd_jobz_from_zval(zval *jobz_zv)
{
    if (Z_TYPE_P(jobz_zv) != IS_STRING) {
        zend_value_error("SVD: jobz must be a string ('N', 'S', or 'A')");
        return 0;
    }

    zend_string *zs = Z_STR_P(jobz_zv);
    if (ZSTR_LEN(zs) != 1) {
        zend_value_error("SVD: invalid jobz (use 'N', 'S', or 'A')");
        return 0;
    }

    char c = ZSTR_VAL(zs)[0];
    if (c != 'N' && c != 'S' && c != 'A') {
        zend_value_error("SVD: invalid jobz (use 'N', 'S', or 'A')");
        return 0;
    }

    return c;
}

/* ---------- DOT ---------- */

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

/* ---------- NORM ---------- */

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

void linear_algebra_vector_normalize_zval(
    zval *x,
    int method,
    zval *return_value
) {
    if (Z_TYPE_P(x) != IS_ARRAY) {
        zend_type_error("normalize(x, method) expects an array");
        return;
    }

    HashTable *ht = Z_ARRVAL_P(x);
    int n = zend_hash_num_elements(ht);

    if (n == 0) {
        zend_value_error("normalize(): vector must not be empty");
        return;
    }

    float *vx = emalloc(sizeof(float) * n);

    int i = 0;
    zval *val;
    ZEND_HASH_FOREACH_VAL(ht, val) {
        vx[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    float norm = 0.0f;

    switch (method) {
        case LA_NORM_L1:
            norm = cblas_sasum(n, vx, 1);
            break;

        case LA_NORM_L2:
            norm = cblas_snrm2(n, vx, 1);
            break;

        case LA_NORM_LINF: {
            int idx = cblas_isamax(n, vx, 1);
            norm = fabsf(vx[idx]);
            break;
        }

        default:
            efree(vx);
            zend_value_error("normalize(): invalid method (0=L1, 1=L2, 2=L∞)");
            return;
    }

    if (norm == 0.0f) {
        efree(vx);
        zend_value_error("normalize(): cannot normalize zero-norm vector");
        return;
    }

    array_init_size(return_value, n);
    for (i = 0; i < n; i++) {
        add_next_index_double(return_value, (double)(vx[i] / norm));
    }

    efree(vx);
}