/* lapack_bridge.h */
#ifndef LAPACK_BRIDGE_H
#define LAPACK_BRIDGE_H

#include <php.h>

/* ABI wrapper (Zephir calls this) */
double linear_algebra_dot_zval(zval *a, zval *b);

double linear_algebra_norm_zval(zval *x, int method);


void fill_float_array_from_php_array(zval *arr, float *out, size_t n);
void fill_matrix_col_major(zval *arr, float *A, int m, int n);

void linear_algebra_svd_zval(
    zval *x,
    int rows,
    int cols,
    char jobz,          // 'N', 'S', 'A'
    zval *return_value
);

#endif