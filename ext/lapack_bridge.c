#include "lapack_bridge.h"
#ifdef USE_SYSTEM_LAPACK
#include <cblas.h>
#else
#error "System OpenBLAS required"
#endif

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