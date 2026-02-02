namespace CoralMedia\LinearAlgebra\Vector;

class Distance
{
    public static function calc(
        array! a,
        array! b,
        int method = 1,
        float p = 3.0
    ) -> float
    {
        return linear_algebra_vector_distance(a, b, method, p);
    }
}