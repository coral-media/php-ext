<?php

/**
 * CoralMedia Element-wise Matrix Operations Test Suite
 *
 * Comprehensive tests for element-wise operations:
 * - matrixAdd, matrixSubtract, matrixHadamard, matrixDivide
 * - matrixScale, matrixAddScalar, matrixMultiplyScalar, matrixDivideScalar
 */

use CoralMedia\LinearAlgebra;

class ElementwiseTestRunner {
    private $passed = 0;
    private $failed = 0;
    private $verbose = false;

    public function __construct(bool $verbose = false) {
        $this->verbose = $verbose;
    }

    public function runTests(): void {
        echo "=== CoralMedia Element-wise Operations Test Suite ===\n\n";

        $this->testBasicOperations();
        $this->testEdgeCases();
        $this->testErrorHandling();
        $this->testMathematicalProperties();
        $this->testIntegration();

        $this->printSummary();
    }

    private function testBasicOperations(): void {
        echo "### Basic Operations ###\n";

        // Test 1: Matrix Addition (2x2)
        $this->assertOperation(
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [6, 8, 10, 12],
            'matrixAdd',
            2, 2,
            "Matrix addition (2×2)"
        );

        // Test 2: Matrix Subtraction (2x2)
        $this->assertOperation(
            [10, 20, 30, 40],
            [1, 2, 3, 4],
            [9, 18, 27, 36],
            'matrixSubtract',
            2, 2,
            "Matrix subtraction (2×2)"
        );

        // Test 3: Hadamard Product (3x2)
        $this->assertOperation(
            [2, 3, 4, 5, 6, 7],
            [1, 2, 3, 4, 5, 6],
            [2, 6, 12, 20, 30, 42],
            'matrixHadamard',
            3, 2,
            "Hadamard product (3×2)"
        );

        // Test 4: Element-wise Division (2x2)
        $this->assertOperation(
            [10, 20, 30, 40],
            [2, 4, 5, 8],
            [5, 5, 6, 5],
            'matrixDivide',
            2, 2,
            "Element-wise division (2×2)"
        );

        // Test 5: Matrix Scaling
        $this->assertScalarOperation(
            [1, 2, 3, 4],
            2.5,
            [2.5, 5, 7.5, 10],
            'matrixScale',
            2, 2,
            "Matrix scaling by 2.5"
        );

        // Test 6: Add Scalar
        $this->assertScalarOperation(
            [1, 2, 3, 4],
            10.0,
            [11, 12, 13, 14],
            'matrixAddScalar',
            2, 2,
            "Add scalar 10 to all elements"
        );

        // Test 7: Multiply by Scalar
        $this->assertScalarOperation(
            [2, 4, 6, 8],
            0.5,
            [1, 2, 3, 4],
            'matrixMultiplyScalar',
            2, 2,
            "Multiply by scalar 0.5"
        );

        // Test 8: Divide by Scalar
        $this->assertScalarOperation(
            [10, 20, 30, 40],
            10.0,
            [1, 2, 3, 4],
            'matrixDivideScalar',
            2, 2,
            "Divide by scalar 10"
        );

        echo "\n";
    }

    private function testEdgeCases(): void {
        echo "### Edge Cases ###\n";

        // Test 9: Zero Matrix Addition
        $this->assertOperation(
            [0, 0, 0, 0],
            [1, 2, 3, 4],
            [1, 2, 3, 4],
            'matrixAdd',
            2, 2,
            "Addition with zero matrix"
        );

        // Test 10: Negative Values
        $this->assertOperation(
            [-1, -2, -3, -4],
            [5, 6, 7, 8],
            [4, 4, 4, 4],
            'matrixAdd',
            2, 2,
            "Addition with negative values"
        );

        // Test 11: Single Element (1x1)
        $this->assertOperation(
            [5],
            [3],
            [15],
            'matrixHadamard',
            1, 1,
            "Hadamard product (1×1)"
        );

        // Test 12: Large Matrix (10x10)
        $large_a = array_fill(0, 100, 2.0);
        $large_b = array_fill(0, 100, 3.0);
        $large_result = array_fill(0, 100, 6.0);
        $this->assertOperation(
            $large_a,
            $large_b,
            $large_result,
            'matrixHadamard',
            10, 10,
            "Hadamard product (10×10)"
        );

        // Test 13: Rectangular Matrix (4x2)
        $this->assertOperation(
            [1, 2, 3, 4, 5, 6, 7, 8],
            [2, 2, 2, 2, 2, 2, 2, 2],
            [2, 4, 6, 8, 10, 12, 14, 16],
            'matrixHadamard',
            4, 2,
            "Hadamard product (4×2)"
        );

        // Test 14: Floating Point Precision
        $this->assertOperation(
            [0.1, 0.2, 0.3],
            [0.1, 0.1, 0.1],
            [0.2, 0.3, 0.4],
            'matrixAdd',
            1, 3,
            "Floating-point precision (1×3)",
            0.0001
        );

        // Test 15: Scale by Zero
        $this->assertScalarOperation(
            [1, 2, 3, 4],
            0.0,
            [0, 0, 0, 0],
            'matrixScale',
            2, 2,
            "Scale by zero"
        );

        // Test 16: Scale by Negative
        $this->assertScalarOperation(
            [1, 2, 3, 4],
            -2.0,
            [-2, -4, -6, -8],
            'matrixScale',
            2, 2,
            "Scale by negative scalar"
        );

        echo "\n";
    }

    private function testErrorHandling(): void {
        echo "### Error Handling ###\n";

        // Test 17: Division by Zero (Element-wise)
        $this->assertError(
            function() {
                LinearAlgebra::matrixDivide([1, 2], [0, 1], 1, 2);
            },
            "ValueError",
            "Element-wise division by zero"
        );

        // Test 18: Division by Zero (Scalar)
        $this->assertError(
            function() {
                LinearAlgebra::matrixDivideScalar([1, 2], 0.0, 1, 2);
            },
            "ValueError",
            "Scalar division by zero"
        );

        // Test 19: Dimension Mismatch (Rows)
        $this->assertError(
            function() {
                LinearAlgebra::matrixAdd([1, 2], [1, 2, 3], 2, 1);
            },
            "ValueError",
            "Dimension mismatch (different rows)"
        );

        // Test 20: Empty Matrix
        $this->assertError(
            function() {
                LinearAlgebra::matrixAdd([], [], 0, 0);
            },
            "ValueError",
            "Empty matrices"
        );

        echo "\n";
    }

    private function testMathematicalProperties(): void {
        echo "### Mathematical Properties ###\n";

        // Test 21: Commutativity of Addition (A + B = B + A)
        $a = [1, 2, 3, 4];
        $b = [5, 6, 7, 8];
        $ab = LinearAlgebra::matrixAdd($a, $b, 2, 2);
        $ba = LinearAlgebra::matrixAdd($b, $a, 2, 2);
        $this->assertArraysEqual($ab, $ba, "Commutativity: A + B = B + A");

        // Test 22: Commutativity of Hadamard Product (A .* B = B .* A)
        $ab_had = LinearAlgebra::matrixHadamard($a, $b, 2, 2);
        $ba_had = LinearAlgebra::matrixHadamard($b, $a, 2, 2);
        $this->assertArraysEqual($ab_had, $ba_had, "Commutativity: A .* B = B .* A");

        // Test 23: Associativity of Addition ((A + B) + C = A + (B + C))
        $c = [9, 10, 11, 12];
        $ab_c = LinearAlgebra::matrixAdd(
            LinearAlgebra::matrixAdd($a, $b, 2, 2),
            $c,
            2, 2
        );
        $a_bc = LinearAlgebra::matrixAdd(
            $a,
            LinearAlgebra::matrixAdd($b, $c, 2, 2),
            2, 2
        );
        $this->assertArraysEqual($ab_c, $a_bc, "Associativity: (A + B) + C = A + (B + C)");

        // Test 24: Distributivity (k * (A + B) = k*A + k*B)
        $k = 3.0;
        $ab_sum = LinearAlgebra::matrixAdd($a, $b, 2, 2);
        $k_ab = LinearAlgebra::matrixScale($ab_sum, $k, 2, 2);
        $ka_kb = LinearAlgebra::matrixAdd(
            LinearAlgebra::matrixScale($a, $k, 2, 2),
            LinearAlgebra::matrixScale($b, $k, 2, 2),
            2, 2
        );
        $this->assertArraysEqual($k_ab, $ka_kb, "Distributivity: k*(A + B) = k*A + k*B");

        echo "\n";
    }

    private function testIntegration(): void {
        echo "### Integration Tests ###\n";

        // Test 25: Chaining Operations
        $a = [1, 2, 3, 4];
        $b = [2, 2, 2, 2];
        $c = [1, 1, 1, 1];

        // (A + B) .* C
        $result = LinearAlgebra::matrixHadamard(
            LinearAlgebra::matrixAdd($a, $b, 2, 2),
            $c,
            2, 2
        );
        $expected = [3, 4, 5, 6]; // (1+2)*1, (2+2)*1, (3+2)*1, (4+2)*1
        $this->assertArraysEqual($result, $expected, "Chaining: (A + B) .* C");

        // Test 26: Combined Scalar and Binary Operations
        $scaled = LinearAlgebra::matrixScale($a, 2.0, 2, 2); // [2, 4, 6, 8]
        $added = LinearAlgebra::matrixAddScalar($scaled, 1.0, 2, 2); // [3, 5, 7, 9]
        $expected2 = [3, 5, 7, 9];
        $this->assertArraysEqual($added, $expected2, "Combined: scale then add scalar");

        // Test 27: Subtract then Divide
        $diff = LinearAlgebra::matrixSubtract([10, 20, 30, 40], [2, 4, 6, 8], 2, 2); // [8, 16, 24, 32]
        $divided = LinearAlgebra::matrixDivideScalar($diff, 8.0, 2, 2); // [1, 2, 3, 4]
        $expected3 = [1, 2, 3, 4];
        $this->assertArraysEqual($divided, $expected3, "Integration: subtract then divide by scalar");

        echo "\n";
    }

    private function assertOperation(
        array $a,
        array $b,
        array $expected,
        string $operation,
        int $rows,
        int $cols,
        string $description,
        float $epsilon = 0.0001
    ): void {
        try {
            $actual = LinearAlgebra::$operation($a, $b, $rows, $cols);

            if ($this->arraysEqual($actual, $expected, $epsilon)) {
                $this->passed++;
                echo "  ✓ {$description}\n";
            } else {
                $this->failed++;
                echo "  ✗ {$description}\n";
                if ($this->verbose) {
                    echo "    Expected: " . json_encode($expected) . "\n";
                    echo "    Got:      " . json_encode($actual) . "\n";
                }
            }
        } catch (Exception $e) {
            $this->failed++;
            echo "  ✗ {$description} - ERROR: {$e->getMessage()}\n";
        }
    }

    private function assertScalarOperation(
        array $a,
        float $scalar,
        array $expected,
        string $operation,
        int $rows,
        int $cols,
        string $description,
        float $epsilon = 0.0001
    ): void {
        try {
            $actual = LinearAlgebra::$operation($a, $scalar, $rows, $cols);

            if ($this->arraysEqual($actual, $expected, $epsilon)) {
                $this->passed++;
                echo "  ✓ {$description}\n";
            } else {
                $this->failed++;
                echo "  ✗ {$description}\n";
                if ($this->verbose) {
                    echo "    Expected: " . json_encode($expected) . "\n";
                    echo "    Got:      " . json_encode($actual) . "\n";
                }
            }
        } catch (Exception $e) {
            $this->failed++;
            echo "  ✗ {$description} - ERROR: {$e->getMessage()}\n";
        }
    }

    private function assertError(
        callable $fn,
        string $expectedError,
        string $description
    ): void {
        try {
            $fn();
            $this->failed++;
            echo "  ✗ {$description} - Expected {$expectedError} but no error thrown\n";
        } catch (TypeError | ValueError $e) {
            if (strpos(get_class($e), $expectedError) !== false) {
                $this->passed++;
                echo "  ✓ {$description}\n";
            } else {
                $this->failed++;
                echo "  ✗ {$description} - Expected {$expectedError}, got " . get_class($e) . "\n";
            }
        } catch (Exception $e) {
            $this->failed++;
            echo "  ✗ {$description} - Unexpected error: {$e->getMessage()}\n";
        }
    }

    private function assertArraysEqual(
        array $actual,
        array $expected,
        string $description,
        float $epsilon = 0.0001
    ): void {
        if ($this->arraysEqual($actual, $expected, $epsilon)) {
            $this->passed++;
            echo "  ✓ {$description}\n";
        } else {
            $this->failed++;
            echo "  ✗ {$description}\n";
            if ($this->verbose) {
                echo "    Expected: " . json_encode($expected) . "\n";
                echo "    Got:      " . json_encode($actual) . "\n";
            }
        }
    }

    private function arraysEqual(array $a, array $b, float $epsilon = 0.0001): bool {
        if (count($a) !== count($b)) {
            return false;
        }

        for ($i = 0; $i < count($a); $i++) {
            if (abs($a[$i] - $b[$i]) > $epsilon) {
                return false;
            }
        }

        return true;
    }

    private function printSummary(): void {
        $total = $this->passed + $this->failed;

        echo "=== Test Summary ===\n";
        echo sprintf("Total tests:  %d\n", $total);
        echo sprintf("✓ Passed:     %d (%.1f%%)\n", $this->passed, ($total > 0 ? ($this->passed / $total) * 100 : 0));
        echo sprintf("✗ Failed:     %d (%.1f%%)\n", $this->failed, ($this->failed / $total) * 100);

        if ($this->failed === 0) {
            echo "\n✅ All tests PASSED!\n";
        } else {
            echo "\n⚠️  Some tests FAILED\n";
        }
    }
}

// Parse command-line arguments
$verbose = in_array('-v', $argv) || in_array('--verbose', $argv);

// Run tests
$runner = new ElementwiseTestRunner($verbose);
$runner->runTests();
