#ifndef LAPACK_BRIDGE_H
#define LAPACK_BRIDGE_H

#include <php.h>

/* Dot: array<float> · array<float> */
double linear_algebra_dot_zval(zval *a, zval *b);

/* Norm: vector norms */
double linear_algebra_norm_zval(zval *x, int method);

/* Helpers */
void fill_float_array_from_php_array(zval *arr, float *out, size_t n);
void fill_matrix_col_major(zval *arr, float *A, int m, int n);

/*
 * SVD wrapper.
 * jobz is a PHP string zval: "N", "S", or "A"
 * - "N": singular values only
 * - "S": reduced U, Vt
 * - "A": full U, Vt
 */
void linear_algebra_svd_zval(
    zval *x,
    int rows,
    int cols,
    zval *jobz_zv,
    zval *return_value
);

#endif /* LAPACK_BRIDGE_H */