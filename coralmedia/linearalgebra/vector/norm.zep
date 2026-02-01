namespace CoralMedia\LinearAlgebra\Vector;

class Norm
{
    public static function calc(array! x, int method = 1) -> array
    {
        return linear_algebra_vector_normalize(x, method);
    }
}