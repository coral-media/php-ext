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
}