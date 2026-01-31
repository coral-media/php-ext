namespace CoralMedia\LinearAlgebra;

class Svd
{
    public static function calc(array x, int rows, int cols)
    {
        /* default: singular values only */
        return self::calcValues(x, rows, cols);
    }

    public static function calcValues(array x, int rows, int cols)
    {
        return linear_algebra_svd(x, rows, cols, "N");
    }

    public static function calcReduced(array x, int rows, int cols)
    {
        return linear_algebra_svd(x, rows, cols, "S");
    }

    public static function calcFull(array x, int rows, int cols)
    {
        return linear_algebra_svd(x, rows, cols, "A");
    }
}