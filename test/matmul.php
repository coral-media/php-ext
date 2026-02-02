#!/usr/bin/env php
<?php

/**
 * Matrix Multiplication Test Suite
 * 
 * Tests the linear_algebra_matmul function with various scenarios
 */

echo "=== CoralMedia Matrix Multiplication Tests ===\n\n";

// Test 1: Basic 2x3 × 3x2 multiplication
echo "Test 1: Basic Matrix Multiplication (2×3) × (3×2)\n";
echo "-----------------------------------------------\n";

$A = [
    1, 2, 3,
    4, 5, 6
];  // 2×3 matrix

$B = [
    7, 8,
    9, 10,
    11, 12
];  // 3×2 matrix

echo "Matrix A (2×3):\n";
echo "[1  2  3]\n";
echo "[4  5  6]\n\n";

echo "Matrix B (3×2):\n";
echo "[7   8]\n";
echo "[9  10]\n";
echo "[11 12]\n\n";

$C = CoralMedia\LinearAlgebra::matmul($A, $B, 2, 3, 2);

echo "Result C = A × B (2×2):\n";
echo sprintf("[%.1f  %.1f]\n", $C[0], $C[1]);
echo sprintf("[%.1f  %.1f]\n", $C[2], $C[3]);
echo "\nExpected:\n";
echo "[58   64]\n";
echo "[139 154]\n\n";

// Test 2: Square matrix multiplication
echo "Test 2: Square Matrix Multiplication (2×2) × (2×2)\n";
echo "---------------------------------------------------\n";

$A2 = [
    1, 2,
    3, 4
];

$B2 = [
    5, 6,
    7, 8
];

echo "Matrix A (2×2):\n";
echo "[1  2]\n";
echo "[3  4]\n\n";

echo "Matrix B (2×2):\n";
echo "[5  6]\n";
echo "[7  8]\n\n";

$C2 = CoralMedia\LinearAlgebra::matmul($A2, $B2, 2, 2, 2);

echo "Result C = A × B (2×2):\n";
echo sprintf("[%.1f  %.1f]\n", $C2[0], $C2[1]);
echo sprintf("[%.1f  %.1f]\n", $C2[2], $C2[3]);
echo "\nExpected:\n";
echo "[19  22]\n";
echo "[43  50]\n\n";

// Test 3: Matrix-vector multiplication (treat vector as column matrix)
echo "Test 3: Matrix-Vector Multiplication (3×3) × (3×1)\n";
echo "----------------------------------------------------\n";

$A3 = [
    1, 0, 0,
    0, 2, 0,
    0, 0, 3
];  // 3×3 identity scaled

$V = [2, 3, 4];  // 3×1 vector

echo "Matrix A (3×3):\n";
echo "[1  0  0]\n";
echo "[0  2  0]\n";
echo "[0  0  3]\n\n";

echo "Vector v (3×1):\n";
echo "[2]\n";
echo "[3]\n";
echo "[4]\n\n";

$result = CoralMedia\LinearAlgebra::matmul($A3, $V, 3, 3, 1);

echo "Result = A × v (3×1):\n";
echo sprintf("[%.1f]\n", $result[0]);
echo sprintf("[%.1f]\n", $result[1]);
echo sprintf("[%.1f]\n", $result[2]);
echo "\nExpected:\n";
echo "[2]\n";
echo "[6]\n";
echo "[12]\n\n";

// Test 4: Identity matrix test
echo "Test 4: Identity Matrix Test (2×2) × (2×2)\n";
echo "-------------------------------------------\n";

$I = [
    1, 0,
    0, 1
];  // Identity matrix

$M = [
    5, 7,
    9, 11
];

$result_identity = CoralMedia\LinearAlgebra::matmul($I, $M, 2, 2, 2);

echo "I × M should equal M:\n";
echo sprintf("[%.1f  %.1f]\n", $result_identity[0], $result_identity[1]);
echo sprintf("[%.1f  %.1f]\n", $result_identity[2], $result_identity[3]);
echo "\nExpected (M):\n";
echo "[5   7]\n";
echo "[9  11]\n\n";

// Test 5: Transpose test
echo "Test 5: Transpose Test (2×3)ᵀ × (2×2)\n";
echo "---------------------------------------\n";

$A5 = [
    1, 2, 3,
    4, 5, 6
];  // 2×3, will be transposed to 3×2

$B5 = [
    1, 0,
    0, 1,
    2, 2
];  // 3×2

echo "Matrix A (2×3) - will be transposed:\n";
echo "[1  2  3]\n";
echo "[4  5  6]\n\n";

echo "Matrix B (3×2):\n";
echo "[1  0]\n";
echo "[0  1]\n";
echo "[2  2]\n\n";

// A^T is 3×2, B is 3×2, so we need A^T × B^T to get valid dimensions
// Actually: A^T (3×2) × B (3×2) won't work. Let me fix this.
// Let's do: A (2×3) × A^T (3×2) = (2×2)

$result_transpose = CoralMedia\LinearAlgebra::matmul($A5, $A5, 2, 3, 2, false, true);

echo "Result = A × Aᵀ (2×2):\n";
echo sprintf("[%.1f  %.1f]\n", $result_transpose[0], $result_transpose[1]);
echo sprintf("[%.1f  %.1f]\n", $result_transpose[2], $result_transpose[3]);
echo "\nExpected:\n";
echo "[14  32]\n";
echo "[32  77]\n\n";

echo "=== All Tests Complete ===\n";