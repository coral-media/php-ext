#include "../lapack_bridge.h"
#include "../linalg_internal.h"

#ifdef USE_SYSTEM_LAPACK
    #include <cblas.h>
#else
    #error "System OpenBLAS required"
#endif

#include <math.h>

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
        case LA_NORM_L1: /* L1 */
            result = cblas_sasum(n, vx, 1);
            break;

        case LA_NORM_L2: /* L2 */
            result = cblas_snrm2(n, vx, 1);
            break;

        case LA_NORM_LINF: /* L-infinity */
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

/* ---------- NORMALIZE ---------- */

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

/* ---------- DISTANCE ---------- */

void linear_algebra_vector_distance_zval(
    zval *a,
    zval *b,
    int method,
    double p,
    zval *return_value
) {
    if (Z_TYPE_P(a) != IS_ARRAY || Z_TYPE_P(b) != IS_ARRAY) {
        zend_type_error("distance(a, b) expects two arrays");
        return;
    }

    HashTable *ha = Z_ARRVAL_P(a);
    HashTable *hb = Z_ARRVAL_P(b);

    int n = zend_hash_num_elements(ha);
    if (n == 0 || n != zend_hash_num_elements(hb)) {
        zend_value_error("distance(): vectors must be same length and non-empty");
        return;
    }

    float *va = emalloc(sizeof(float) * n);
    float *vb = emalloc(sizeof(float) * n);

    int i = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(ha, val) {
        va[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    i = 0;
    ZEND_HASH_FOREACH_VAL(hb, val) {
        vb[i++] = (float) zval_get_double(val);
    } ZEND_HASH_FOREACH_END();

    double result = 0.0;

    switch (method) {
        case LA_DIST_L1:
            for (i = 0; i < n; i++) {
                result += fabs(va[i] - vb[i]);
            }
            break;

        case LA_DIST_L2:
            for (i = 0; i < n; i++) {
                double d = va[i] - vb[i];
                result += d * d;
            }
            result = sqrt(result);
            break;

        case LA_DIST_LP:
            if (p < 1.0) {
                efree(va); efree(vb);
                zend_value_error("distance(): Minkowski requires p >= 1");
                return;
            }
            for (i = 0; i < n; i++) {
                result += pow(fabs(va[i] - vb[i]), p);
            }
            result = pow(result, 1.0 / p);
            break;
        case LA_DIST_COS:
            double dot = 0.0;
            double na  = 0.0;
            double nb  = 0.0;

            for (i = 0; i < n; i++) {
                dot += va[i] * vb[i];
                na  += va[i] * va[i];
                nb  += vb[i] * vb[i];
            }

            if (na == 0.0 || nb == 0.0) {
                efree(va); efree(vb);
                zend_value_error("distance(): cosine distance undefined for zero-norm vector");
                return;
            }

            result = 1.0 - (dot / (sqrt(na) * sqrt(nb)));
            break;
        default:
            efree(va); efree(vb);
            zend_value_error("distance(): invalid method");
            return;
    }

    efree(va);
    efree(vb);

    ZVAL_DOUBLE(return_value, result);
}
