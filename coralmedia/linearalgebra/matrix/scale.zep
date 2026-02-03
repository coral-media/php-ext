namespace CoralMedia\LinearAlgebra\Matrix;

class Scale
{
    /**
     * Multiply matrix by scalar: C[i] = scalar × A[i]
     *
     * @param array a - Matrix A as flat row-major array
     * @param float scalar - Scalar multiplier
     * @param int rows - Number of rows
     * @param int cols - Number of columns
     * @return array - Result matrix C as flat row-major array
     */
    public static function calc(array! a, float scalar, int rows, int cols) -> array
    {
        // intercepted by optimizer
        return linear_algebra_matrix_scale(a, scalar, rows, cols);
    }

    /**
     * Add scalar to all matrix elements: C[i] = A[i] + scalar
     *
     * @param array a - Matrix A as flat row-major array
     * @param float scalar - Scalar to add
     * @param int rows - Number of rows
     * @param int cols - Number of columns
     * @return array - Result matrix C as flat row-major array
     */
    public static function addScalar(array! a, float scalar, int rows, int cols) -> array
    {
        // intercepted by optimizer
        return linear_algebra_matrix_add_scalar(a, scalar, rows, cols);
    }

    /**
     * Multiply matrix by scalar (alias for calc): C[i] = scalar × A[i]
     *
     * @param array a - Matrix A as flat row-major array
     * @param float scalar - Scalar multiplier
     * @param int rows - Number of rows
     * @param int cols - Number of columns
     * @return array - Result matrix C as flat row-major array
     */
    public static function multiplyScalar(array! a, float scalar, int rows, int cols) -> array
    {
        // intercepted by optimizer
        return linear_algebra_matrix_multiply_scalar(a, scalar, rows, cols);
    }

    /**
     * Divide all matrix elements by scalar: C[i] = A[i] / scalar
     *
     * @param array a - Matrix A as flat row-major array
     * @param float scalar - Scalar divisor
     * @param int rows - Number of rows
     * @param int cols - Number of columns
     * @return array - Result matrix C as flat row-major array
     */
    public static function divideScalar(array! a, float scalar, int rows, int cols) -> array
    {
        // intercepted by optimizer
        return linear_algebra_matrix_divide_scalar(a, scalar, rows, cols);
    }
}
