namespace CoralMedia\LinearAlgebra;

class Norm
{
    /**
     * @param array x   Flat array (row-major)
     * @param int rows
     * @param int cols
     * @param int method  (0 = L1, 1 = L2, 2 = Frobenius)
     */
    public static function calc(array! x, int method) -> float
    {
        return linear_algebra_norm(x, method);
    }
}
