namespace CoralMedia;

class LinearAlgebra
{
    public static function dot(array! a, array! b) -> float
    {
        return LinearAlgebra\Dot::calc(a, b);
    }

    public static function norm(array! x, int method) -> float
    {
        return LinearAlgebra\Norm::calc(x, method);
    }
}