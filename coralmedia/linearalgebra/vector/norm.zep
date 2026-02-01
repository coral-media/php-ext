namespace CoralMedia\LinearAlgebra\Vector;

class Norm
{
    public static function calc(array! x) -> array
    {
        return linear_algebra_vector_normalize(x);
    }
}