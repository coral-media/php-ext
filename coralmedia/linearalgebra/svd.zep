namespace CoralMedia\LinearAlgebra;

class Svd
{
    public static function calc(array! x, int rows, int cols, string jobz = "N")
    {
        return linear_algebra_svd(x, rows, cols, jobz);
    }
}