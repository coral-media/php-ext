namespace CoralMedia;

use CoralMedia\LinearAlgebra\Vector;
use CoralMedia\LinearAlgebra\Matrix;

class LinearAlgebra
{
    public static function dot(array! a, array! b) -> float
    {
        return Vector\Dot::calc(a, b);
    }

    public static function norm(array! x, int method = Constants::LA_NORM_L2) -> float
    {
        return Vector\Norm::calc(x, method);
    }

    public static function normalize(array! x, int method = Constants::LA_NORM_L2) -> array
    {
        return Vector\Normalize::calc(x, method);
    }

    public static function svd(array x, int rows, int cols, string jobz = Constants::LA_SVD_VALUES) -> array {
        return Matrix\Svd::calc(x, rows, cols, jobz);
    }

    public static function distance(
        array! a,
        array! b,
        int method = Constants::LA_DIST_L2,
        float p = 3.0
    ) -> float {
        return Vector\Distance::calc(a, b, method, p);
    }

    public static function matmul(
        array! a,
        array! b,
        int m,
        int n,
        int k,
        bool transpose_a = false,
        bool transpose_b = false
    ) -> array {
        return Matrix\Matmul::calc(a, b, m, n, k, transpose_a, transpose_b);
    }

    public static function matrixAdd(array! a, array! b, int rows, int cols) -> array {
        return Matrix\Add::calc(a, b, rows, cols);
    }

    public static function matrixSubtract(array! a, array! b, int rows, int cols) -> array {
        return Matrix\Subtract::calc(a, b, rows, cols);
    }

    public static function matrixHadamard(array! a, array! b, int rows, int cols) -> array {
        return Matrix\Hadamard::calc(a, b, rows, cols);
    }

    public static function matrixDivide(array! a, array! b, int rows, int cols) -> array {
        return Matrix\Divide::calc(a, b, rows, cols);
    }

    public static function matrixScale(array! a, float scalar, int rows, int cols) -> array {
        return Matrix\Scale::calc(a, scalar, rows, cols);
    }

    public static function matrixAddScalar(array! a, float scalar, int rows, int cols) -> array {
        return Matrix\Scale::addScalar(a, scalar, rows, cols);
    }

    public static function matrixMultiplyScalar(array! a, float scalar, int rows, int cols) -> array {
        return Matrix\Scale::multiplyScalar(a, scalar, rows, cols);
    }

    public static function matrixDivideScalar(array! a, float scalar, int rows, int cols) -> array {
        return Matrix\Scale::divideScalar(a, scalar, rows, cols);
    }
}