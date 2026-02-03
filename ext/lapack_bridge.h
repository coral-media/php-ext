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

void linear_algebra_matmul_zval(
    zval *a,
    zval *b,
    int m,
    int n,
    int k,
    zend_bool transpose_a,
    zend_bool transpose_b,
    zval *return_value
);

/* Element-wise binary operations */
void linear_algebra_matrix_add_zval(zval *a, zval *b, int rows, int cols, zval *return_value);
void linear_algebra_matrix_subtract_zval(zval *a, zval *b, int rows, int cols, zval *return_value);
void linear_algebra_matrix_hadamard_zval(zval *a, zval *b, int rows, int cols, zval *return_value);
void linear_algebra_matrix_divide_zval(zval *a, zval *b, int rows, int cols, zval *return_value);

/* Scalar operations */
void linear_algebra_matrix_scale_zval(zval *a, double scalar, int rows, int cols, zval *return_value);
void linear_algebra_matrix_add_scalar_zval(zval *a, double scalar, int rows, int cols, zval *return_value);
void linear_algebra_matrix_multiply_scalar_zval(zval *a, double scalar, int rows, int cols, zval *return_value);
void linear_algebra_matrix_divide_scalar_zval(zval *a, double scalar, int rows, int cols, zval *return_value);

#endif /* LAPACK_BRIDGE_H */