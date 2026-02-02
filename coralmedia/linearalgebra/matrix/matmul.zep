namespace CoralMedia\LinearAlgebra\Matrix;

class Matmul
{
    /**
     * Matrix multiplication: C = A × B
     * 
     * @param array a - Matrix A as flat row-major array
     * @param array b - Matrix B as flat row-major array
     * @param int m - Number of rows in A
     * @param int n - Number of columns in A (= rows in B)
     * @param int k - Number of columns in B
     * @param bool transpose_a - Whether to transpose A
     * @param bool transpose_b - Whether to transpose B
     * @return array - Result matrix C as flat row-major array (m × k)
     */
    public static function calc(
        array! a,
        array! b,
        int m,
        int n,
        int k,
        bool transpose_a = false,
        bool transpose_b = false
    ) -> array
    {
        // intercepted by optimizer
        return linear_algebra_matmul(a, b, m, n, k, transpose_a, transpose_b);
    }
}
