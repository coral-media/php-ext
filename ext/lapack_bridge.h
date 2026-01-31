/* lapack_bridge.h */
#ifndef LAPACK_BRIDGE_H
#define LAPACK_BRIDGE_H

#include <php.h>

/* ABI wrapper (Zephir calls this) */
double linear_algebra_dot_zval(zval *a, zval *b);

double linear_algebra_norm_zval(zval *x, int method);

#endif