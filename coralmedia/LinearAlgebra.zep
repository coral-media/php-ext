namespace CoralMedia;

use CoralMedia\LinearAlgebra\Dot;
use CoralMedia\LinearAlgebra\Norm;
use CoralMedia\LinearAlgebra\Svd;

class LinearAlgebra
{
    /**
     * Computes the dot product of two numeric vectors.
     */
    public static function dot(array! a, array! b) -> float
    {
        return Dot::calc(a, b);
    }

    /**
     * Computes a vector norm.
     *
     * method:
     *  0 = L₁
     *  1 = L₂
     */
    public static function norm(array! x, int method) -> float
    {
        return Norm::calc(x, method);
    }

    public static function svd(array x, int rows, int cols)
    {
        /* safe default */
        return LinearAlgebra\Svd::calc(x, rows, cols);
    }

    public static function svdValues(array x, int rows, int cols)
    {
        return LinearAlgebra\Svd::calcValues(x, rows, cols);
    }

    public static function svdReduced(array x, int rows, int cols)
    {
        return LinearAlgebra\Svd::calcReduced(x, rows, cols);
    }

    public static function svdFull(array x, int rows, int cols)
    {
        return LinearAlgebra\Svd::calcFull(x, rows, cols);
    }
}