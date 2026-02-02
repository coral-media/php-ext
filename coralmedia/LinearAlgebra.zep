namespace CoralMedia;

use CoralMedia\LinearAlgebra\Dot;
use CoralMedia\LinearAlgebra\Norm;
use CoralMedia\LinearAlgebra\Svd;
use CoralMedia\LinearAlgebra\Vector\Normalize;

class LinearAlgebra
{
    public static function dot(array! a, array! b) -> float
    {
        return Dot::calc(a, b);
    }

    public static function norm(array! x, int method = Constants::LA_NORM_L2) -> float
    {
        return Norm::calc(x, method);
    }

    public static function svd(array x, int rows, int cols, string jobz = Constants::LA_SVD_VALUES) -> array
    {
        return Svd::calc(x, rows, cols, jobz);
    }

    public static function normalize(array! x, int method = Constants::LA_NORM_L2) -> array
    {
        return Normalize::calc(x, method);
    }
}