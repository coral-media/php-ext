#ifndef LAPACK_BRIDGE_H
#define LAPACK_BRIDGE_H

#include <php.h>

void fill_float_array_from_php_array(zval *arr, float *out, size_t n);
void fill_matrix_col_major(zval *arr, float *A, int m, int n);

double linear_algebra_dot_zval(zval *a, zval *b);

double linear_algebra_norm_zval(zval *x, int method);

void linear_algebra_svd_zval(
    zval *x,
    int rows,
    int cols,
    zval *jobz_zv,
    zval *return_value
);

void linear_algebra_vector_normalize_zval(
    zval *x,
    int method,
    zval *return_value
);

void linear_algebra_vector_distance_zval(
    zval *a,
    zval *b,
    int method,
    double p,
    zval *return_value
);

#endif /* LAPACK_BRIDGE_H */