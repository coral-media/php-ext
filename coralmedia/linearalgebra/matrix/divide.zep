namespace CoralMedia\LinearAlgebra\Matrix;

class Divide
{
    /**
     * Element-wise matrix division: C[i] = A[i] / B[i]
     *
     * @param array a - Matrix A as flat row-major array
     * @param array b - Matrix B as flat row-major array
     * @param int rows - Number of rows
     * @param int cols - Number of columns
     * @return array - Result matrix C as flat row-major array
     */
    public static function calc(array! a, array! b, int rows, int cols) -> array
    {
        // intercepted by optimizer
        return linear_algebra_matrix_divide(a, b, rows, cols);
    }
}
