#ifndef LINALG_INTERNAL_H
#define LINALG_INTERNAL_H

#include <php.h>

/* Norm type constants */
#define LA_NORM_L1   0
#define LA_NORM_L2   1
#define LA_NORM_LINF 2

/* SVD job type constants */
#define LA_SVD_VALUES  'N'
#define LA_SVD_REDUCED 'S'
#define LA_SVD_FULL    'A'

/* Distance metric constants */
#define LA_DIST_L1  0
#define LA_DIST_L2  1
#define LA_DIST_LP  2
#define LA_DIST_COS 3

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

/* Internal helper functions */
char svd_jobz_from_zval(zval *jobz_zv);

#endif /* LINALG_INTERNAL_H */
