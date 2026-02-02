#!/usr/bin/env php
<?php

/**
 * Matrix Multiplication Examples
 * 
 * Demonstrates practical applications of matrix multiplication
 */

echo "=== CoralMedia Matrix Multiplication Examples ===\n\n";

// Helper function to print matrix
function printMatrix(array $matrix, int $rows, int $cols, string $name = "Matrix"): void
{
    echo "{$name} ({$rows}×{$cols}):\n";
    for ($i = 0; $i < $rows; $i++) {
        echo "  [";
        for ($j = 0; $j < $cols; $j++) {
            echo sprintf("%6.2f", $matrix[$i * $cols + $j]);
            echo ($j < $cols - 1) ? "  " : "";
        }
        echo "]\n";
    }
    echo "\n";
}

// Example 1: Basic matrix multiplication
echo "Example 1: Basic Matrix Multiplication\n";
echo str_repeat('-', 50) . "\n\n";

$A = [1, 2, 3, 4, 5, 6];
$B = [7, 8, 9, 10, 11, 12];

printMatrix($A, 2, 3, "Matrix A");
printMatrix($B, 3, 2, "Matrix B");

$C = CoralMedia\LinearAlgebra::matmul($A, $B, 2, 3, 2);
printMatrix($C, 2, 2, "Result C = A × B");

echo "Calculation:\n";
echo "  C[0,0] = 1×7 + 2×9 + 3×11 = 7 + 18 + 33 = 58\n";
echo "  C[0,1] = 1×8 + 2×10 + 3×12 = 8 + 20 + 36 = 64\n";
echo "  C[1,0] = 4×7 + 5×9 + 6×11 = 28 + 45 + 66 = 139\n";
echo "  C[1,1] = 4×8 + 5×10 + 6×12 = 32 + 50 + 72 = 154\n\n";

// Example 2: Linear transformation (rotation)
echo "Example 2: 2D Rotation Transformation\n";
echo str_repeat('-', 50) . "\n\n";

$angle = pi() / 4; // 45 degrees
$rotation = [
    cos($angle), -sin($angle),
    sin($angle), cos($angle)
];

$points = [
    1, 0,  // Point 1: (1, 0)
    0, 1,  // Point 2: (0, 1)
    1, 1   // Point 3: (1, 1)
];

printMatrix($rotation, 2, 2, "Rotation matrix (45°)");
printMatrix($points, 3, 2, "Original points");

$rotated = CoralMedia\LinearAlgebra::matmul($points, $rotation, 3, 2, 2, false, true);
printMatrix($rotated, 3, 2, "Rotated points");

echo "Point transformations:\n";
for ($i = 0; $i < 3; $i++) {
    echo sprintf("  Point %d: (%.2f, %.2f) → (%.2f, %.2f)\n",
        $i + 1,
        $points[$i * 2],
        $points[$i * 2 + 1],
        $rotated[$i * 2],
        $rotated[$i * 2 + 1]
    );
}
echo "\n";

// Example 3: System of linear equations
echo "Example 3: Solving Linear Systems (Ax = b)\n";
echo str_repeat('-', 50) . "\n\n";

echo "System of equations:\n";
echo "  2x + 3y = 13\n";
echo "  4x + 5y = 23\n\n";

// Coefficient matrix and constants
$A_sys = [2, 3, 4, 5];
$b = [13, 23];

// For demonstration, multiply by known solution
$x_solution = [2, 3]; // x=2, y=3

$result = CoralMedia\LinearAlgebra::matmul($A_sys, $x_solution, 2, 2, 1);

echo "Verification: A × [2, 3]ᵀ = [" . implode(', ', $result) . "]ᵀ\n";
echo "  2×2 + 3×3 = 4 + 9 = 13 ✓\n";
echo "  4×2 + 5×3 = 8 + 15 = 23 ✓\n\n";

// Example 4: Neural network - forward pass
echo "Example 4: Neural Network Forward Pass\n";
echo str_repeat('-', 50) . "\n\n";

// 3 samples, 4 features each
$X = [
    1.0, 2.0, 3.0, 4.0,   // Sample 1
    2.0, 3.0, 4.0, 5.0,   // Sample 2
    3.0, 4.0, 5.0, 6.0    // Sample 3
];

// Weight matrix: 4 features → 3 neurons
$W = [
    0.1, 0.2, 0.3,
    0.4, 0.5, 0.6,
    0.7, 0.8, 0.9,
    1.0, 1.1, 1.2
];

printMatrix($X, 3, 4, "Input X (3 samples)");
printMatrix($W, 4, 3, "Weights W");

$output = CoralMedia\LinearAlgebra::matmul($X, $W, 3, 4, 3);
printMatrix($output, 3, 3, "Output (3 samples × 3 neurons)");

echo "Each row is the pre-activation output for one sample\n\n";

// Example 5: Image convolution (simplified via im2col)
echo "Example 5: Image Patch Extraction (im2col concept)\n";
echo str_repeat('-', 50) . "\n\n";

echo "Concept: Extract 2×2 patches from a 3×3 image\n";
echo "Original image:\n";
echo "  [1  2  3]\n";
echo "  [4  5  6]\n";
echo "  [7  8  9]\n\n";

// Extracted patches as columns (im2col format)
$patches = [
    1, 2, 4, 5,  // Top-left patch
    2, 3, 5, 6,  // Top-right patch
    4, 5, 7, 8,  // Bottom-left patch
    5, 6, 8, 9   // Bottom-right patch
];

// Simple filter (detects edges)
$filter = [1, -1, -1, 1];

printMatrix($patches, 4, 4, "Patches (4 patches of 4 pixels)");
printMatrix($filter, 1, 4, "Filter (1×4)");

$filtered = CoralMedia\LinearAlgebra::matmul($filter, $patches, 1, 4, 4);
printMatrix($filtered, 1, 4, "Filter response");

echo "Each value is the filter response for one patch\n\n";

// Example 6: Covariance matrix
echo "Example 6: Computing Covariance Matrix\n";
echo str_repeat('-', 50) . "\n\n";

// Data matrix: 5 samples, 3 features (centered)
$X_centered = [
    -2, -1,  0,
    -1,  0,  1,
     0,  1,  0,
     1,  0, -1,
     2,  0,  0
];

printMatrix($X_centered, 5, 3, "Centered data X");

// Covariance = (1/n) × X^T × X
$cov = CoralMedia\LinearAlgebra::matmul($X_centered, $X_centered, 5, 3, 3, true, false);

// Scale by 1/n
$n = 5;
for ($i = 0; $i < count($cov); $i++) {
    $cov[$i] /= $n;
}

printMatrix($cov, 3, 3, "Covariance matrix (X^T × X) / n");

echo "Diagonal: variance of each feature\n";
echo "Off-diagonal: covariance between features\n\n";

// Example 7: Matrix chain multiplication
echo "Example 7: Matrix Chain Multiplication\n";
echo str_repeat('-', 50) . "\n\n";

$A = [1, 2, 3, 4];           // 2×2
$B = [1, 0, 0, 1];           // 2×2 (identity)
$C = [2, 0, 0, 2];           // 2×2 (scale by 2)

printMatrix($A, 2, 2, "Matrix A");
printMatrix($B, 2, 2, "Matrix B (identity)");
printMatrix($C, 2, 2, "Matrix C (scale)");

// Compute A × B × C
$AB = CoralMedia\LinearAlgebra::matmul($A, $B, 2, 2, 2);
$ABC = CoralMedia\LinearAlgebra::matmul($AB, $C, 2, 2, 2);

printMatrix($ABC, 2, 2, "Result (A × B × C)");

echo "Since B is identity: A × B × C = A × C = A scaled by 2\n\n";

// Example 8: Polynomial evaluation via matrix
echo "Example 8: Polynomial Evaluation (Vandermonde)\n";
echo str_repeat('-', 50) . "\n\n";

// Evaluate polynomials at x = [1, 2, 3]
// Using Vandermonde matrix for degrees 0, 1, 2
$x_values = [
    1, 1, 1,   // x=1: [1, 1, 1] = [x^0, x^1, x^2]
    1, 2, 4,   // x=2: [1, 2, 4]
    1, 3, 9    // x=3: [1, 3, 9]
];

// Coefficients for polynomial: 2 + 3x + 1x^2
$coeffs = [2, 3, 1];

printMatrix($x_values, 3, 3, "Vandermonde matrix");
echo "Coefficients: [2, 3, 1] → polynomial 2 + 3x + x²\n\n";

$poly_values = CoralMedia\LinearAlgebra::matmul($x_values, $coeffs, 3, 3, 1);

echo "Polynomial values:\n";
for ($i = 0; $i < 3; $i++) {
    $x = $i + 1;
    echo sprintf("  f(%d) = 2 + 3×%d + %d² = %.0f\n", 
        $x, $x, $x, $poly_values[$i]);
}
echo "\n";

// Example 9: Batch processing
echo "Example 9: Batch Processing Efficiency\n";
echo str_repeat('-', 50) . "\n\n";

$batch_size = 100;
$input_dim = 50;
$output_dim = 20;

echo "Processing {$batch_size} samples at once:\n";
echo "  Input:   {$batch_size} × {$input_dim}\n";
echo "  Weights: {$input_dim} × {$output_dim}\n";
echo "  Output:  {$batch_size} × {$output_dim}\n\n";

// Generate random data
$X_batch = [];
$W_batch = [];

for ($i = 0; $i < $batch_size * $input_dim; $i++) {
    $X_batch[] = mt_rand(0, 100) / 100.0;
}

for ($i = 0; $i < $input_dim * $output_dim; $i++) {
    $W_batch[] = mt_rand(-10, 10) / 100.0;
}

$start = microtime(true);
$Y_batch = CoralMedia\LinearAlgebra::matmul($X_batch, $W_batch, $batch_size, $input_dim, $output_dim);
$duration = (microtime(true) - $start) * 1000;

echo sprintf("Batch computation completed in %.2f ms\n", $duration);
echo sprintf("Processing rate: %.0f samples/sec\n", $batch_size / ($duration / 1000));
echo sprintf("Total operations: %s FLOPs\n", 
    number_format(2 * $batch_size * $input_dim * $output_dim));
echo "\n";

// Example 10: Matrix transpose via multiplication
echo "Example 10: Transpose Operations\n";
echo str_repeat('-', 50) . "\n\n";

$M = [1, 2, 3, 4, 5, 6];

printMatrix($M, 2, 3, "Original matrix M");

// Compute M × M^T (Gram matrix)
$MMT = CoralMedia\LinearAlgebra::matmul($M, $M, 2, 3, 2, false, true);
printMatrix($MMT, 2, 2, "M × M^T (Gram matrix)");

// Compute M^T × M
$MTM = CoralMedia\LinearAlgebra::matmul($M, $M, 3, 2, 3, true, false);
printMatrix($MTM, 3, 3, "M^T × M");

echo "M × M^T gives a (2×2) matrix (outer product structure)\n";
echo "M^T × M gives a (3×3) matrix (feature covariance structure)\n\n";

echo "=== Examples Complete ===\n";