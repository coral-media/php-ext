namespace CoralMedia\LinearAlgebra\Vector;

class Dot
{
    public static function calc(array! a, array! b) -> float
    {
        // intercepted by optimizer
        return linear_algebra_dot(a, b);
    }
}