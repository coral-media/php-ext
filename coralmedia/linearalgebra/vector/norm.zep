namespace CoralMedia\LinearAlgebra\Vector;

class Norm
{
    public static function calc(array! x, int method) -> float
    {
        return linear_algebra_norm(x, method);
    }
}
