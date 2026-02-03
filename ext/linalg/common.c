#include "../lapack_bridge.h"
#include "../linalg_internal.h"

#ifdef USE_SYSTEM_LAPACK
    #include <cblas.h>
#else
    #error "System OpenBLAS required"
#endif

#include <math.h>
#include <string.h>

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

char svd_jobz_from_zval(zval *jobz_zv)
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
