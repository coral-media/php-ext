#include "php.h"
#include "ccontrol_bridge.h"

#include "ccontrol/src/CControl/Sources/LinearAlgebra/linearalgebra.h"

static void fill_float_array_from_php_array(zval *arr_zv, float *out, size_t n)
{
    size_t i = 0;
    zval *val;

    ZEND_HASH_FOREACH_VAL(Z_ARRVAL_P(arr_zv), val)
    {
        if (i >= n)
            break;
        out[i++] = (float)zval_get_double(val);
    }
    ZEND_HASH_FOREACH_END();
}

double linear_algebra_dot(zval *a_zv, zval *b_zv)
{
    if (Z_TYPE_P(a_zv) != IS_ARRAY || Z_TYPE_P(b_zv) != IS_ARRAY)
    {
        zend_type_error("dot(a,b) expects two arrays");
        return 0.0;
    }

    size_t na = zend_hash_num_elements(Z_ARRVAL_P(a_zv));
    size_t nb = zend_hash_num_elements(Z_ARRVAL_P(b_zv));

    if (na == 0 || nb == 0 || na != nb)
    {
        zend_value_error("dot(a,b) requires same-length non-empty arrays");
        return 0.0;
    }

    float *a = (float *)emalloc(sizeof(float) * na);
    float *b = (float *)emalloc(sizeof(float) * nb);

    fill_float_array_from_php_array(a_zv, a, na);
    fill_float_array_from_php_array(b_zv, b, nb);

    float r = dot(a, b, na);

    efree(a);
    efree(b);

    return (double)r;
}