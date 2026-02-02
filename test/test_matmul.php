#!/usr/bin/env php
<?php

/**
 * Matrix Multiplication Test Suite
 * 
 * Comprehensive testing of CoralMedia's matrix multiplication implementation
 */

class MatmulTestRunner
{
    private $verbose = false;
    private $passed = 0;
    private $failed = 0;
    private $errors = [];

    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function runTests(): void
    {
        echo "=== CoralMedia Matrix Multiplication Test Suite ===\n\n";

        $this->testBasicMultiplication();
        $this->testSquareMatrices();
        $this->testMatrixVector();
        $this->testIdentityMatrix();
        $this->testZeroMatrix();
        $this->testTranspose();
        $this->testRectangularMatrices();
        $this->testSingleElement();
        $this->testCommutativeProperty();
        $this->testAssociativeProperty();

        $this->printSummary();
    }

    private function testBasicMultiplication(): void
    {
        echo "Test 1: Basic Matrix Multiplication\n";
        echo str_repeat('-', 50) . "\n";

        // 2×3 × 3×2 = 2×2
        $A = [1, 2, 3, 4, 5, 6];
        $B = [7, 8, 9, 10, 11, 12];
        $expected = [58, 64, 139, 154];

        $this->assertMatmul($A, $B, 2, 3, 2, $expected,
            "(2×3) × (3×2) = (2×2)"
        );

        // 3×2 × 2×3 = 3×3
        $A2 = [1, 2, 3, 4, 5, 6];
        $B2 = [7, 8, 9, 10, 11, 12];
        $expected2 = [29, 32, 35, 65, 72, 79, 101, 112, 123];

        $this->assertMatmul($A2, $B2, 3, 2, 3, $expected2,
            "(3×2) × (2×3) = (3×3)"
        );

        echo "\n";
    }

    private function testSquareMatrices(): void
    {
        echo "Test 2: Square Matrix Multiplication\n";
        echo str_repeat('-', 50) . "\n";

        // 2×2 × 2×2 = 2×2
        $A = [1, 2, 3, 4];
        $B = [5, 6, 7, 8];
        $expected = [19, 22, 43, 50];

        $this->assertMatmul($A, $B, 2, 2, 2, $expected,
            "(2×2) × (2×2) = (2×2)"
        );

        // 3×3 × 3×3 = 3×3
        $A2 = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $B2 = [9, 8, 7, 6, 5, 4, 3, 2, 1];
        $expected2 = [30, 24, 18, 84, 69, 54, 138, 114, 90];

        $this->assertMatmul($A2, $B2, 3, 3, 3, $expected2,
            "(3×3) × (3×3) = (3×3)"
        );

        echo "\n";
    }

    private function testMatrixVector(): void
    {
        echo "Test 3: Matrix-Vector Multiplication\n";
        echo str_repeat('-', 50) . "\n";

        // 3×3 × 3×1 = 3×1
        $A = [1, 0, 0, 0, 2, 0, 0, 0, 3];
        $v = [2, 3, 4];
        $expected = [2, 6, 12];

        $this->assertMatmul($A, $v, 3, 3, 1, $expected,
            "Diagonal matrix × vector"
        );

        // 2×3 × 3×1 = 2×1
        $A2 = [1, 2, 3, 4, 5, 6];
        $v2 = [2, 1, 3];
        $expected2 = [13, 31];

        $this->assertMatmul($A2, $v2, 2, 3, 1, $expected2,
            "(2×3) × (3×1) = (2×1)"
        );

        echo "\n";
    }

    private function testIdentityMatrix(): void
    {
        echo "Test 4: Identity Matrix Properties\n";
        echo str_repeat('-', 50) . "\n";

        // I × A = A
        $I = [1, 0, 0, 1];
        $A = [5, 7, 9, 11];
        $expected = [5, 7, 9, 11];

        $this->assertMatmul($I, $A, 2, 2, 2, $expected,
            "I(2×2) × A(2×2) = A"
        );

        // A × I = A
        $this->assertMatmul($A, $I, 2, 2, 2, $expected,
            "A(2×2) × I(2×2) = A"
        );

        // 3×3 identity
        $I3 = [1, 0, 0, 0, 1, 0, 0, 0, 1];
        $A3 = [2, 3, 4, 5, 6, 7, 8, 9, 10];
        $expected3 = [2, 3, 4, 5, 6, 7, 8, 9, 10];

        $this->assertMatmul($I3, $A3, 3, 3, 3, $expected3,
            "I(3×3) × A(3×3) = A"
        );

        echo "\n";
    }

    private function testZeroMatrix(): void
    {
        echo "Test 5: Zero Matrix Properties\n";
        echo str_repeat('-', 50) . "\n";

        // Zero matrix × any matrix = zero
        $Z = [0, 0, 0, 0];
        $A = [5, 7, 9, 11];
        $expected = [0, 0, 0, 0];

        $this->assertMatmul($Z, $A, 2, 2, 2, $expected,
            "Zero(2×2) × A(2×2) = Zero"
        );

        // Any matrix × zero matrix = zero
        $this->assertMatmul($A, $Z, 2, 2, 2, $expected,
            "A(2×2) × Zero(2×2) = Zero"
        );

        echo "\n";
    }

    private function testTranspose(): void
    {
        echo "Test 6: Transpose Operations\n";
        echo str_repeat('-', 50) . "\n";

        // A × B^T
        $A = [1, 2, 3, 4];
        $B = [5, 7, 6, 8];  // Will be transposed to [[5,6],[7,8]]
        $expected = [19, 23, 43, 51];

        $this->assertMatmul($A, $B, 2, 2, 2, $expected,
            "A(2×2) × B^T(2×2)", false, true
        );

        // A^T × B
        $A2 = [1, 3, 2, 4];  // Will be transposed
        $B2 = [5, 6, 7, 8];
        $expected2 = [19, 22, 43, 50];

        $this->assertMatmul($A2, $B2, 2, 2, 2, $expected2,
            "A^T(2×2) × B(2×2)", true, false
        );

        // A^T × B^T
        $A3 = [1, 3, 2, 4];
        $B3 = [5, 7, 6, 8];
        $expected3 = [19, 23, 43, 51];

        $this->assertMatmul($A3, $B3, 2, 2, 2, $expected3,
            "A^T(2×2) × B^T(2×2)", true, true
        );

        echo "\n";
    }

    private function testRectangularMatrices(): void
    {
        echo "Test 7: Rectangular Matrices\n";
        echo str_repeat('-', 50) . "\n";

        // Tall × wide
        // 4×2 × 2×3 = 4×3
        $A = [1, 2, 3, 4, 5, 6, 7, 8];
        $B = [1, 2, 3, 4, 5, 6];
        $expected = [9, 12, 15, 19, 26, 33, 29, 40, 51, 39, 54, 69];

        $this->assertMatmul($A, $B, 4, 2, 3, $expected,
            "(4×2) × (2×3) = (4×3)"
        );

        // Wide × tall
        // 2×4 × 4×2 = 2×2
        $A2 = [1, 2, 3, 4, 5, 6, 7, 8];
        $B2 = [1, 2, 3, 4, 5, 6, 7, 8];
        $expected2 = [50, 60, 114, 140];

        $this->assertMatmul($A2, $B2, 2, 4, 2, $expected2,
            "(2×4) × (4×2) = (2×2)"
        );

        echo "\n";
    }

    private function testSingleElement(): void
    {
        echo "Test 8: Single Element (1×1) Matrices\n";
        echo str_repeat('-', 50) . "\n";

        // Scalar multiplication via 1×1 matrices
        $A = [5];
        $B = [3];
        $expected = [15];

        $this->assertMatmul($A, $B, 1, 1, 1, $expected,
            "(1×1) × (1×1) = (1×1)"
        );

        echo "\n";
    }

    private function testCommutativeProperty(): void
    {
        echo "Test 9: Non-Commutative Property (A×B ≠ B×A)\n";
        echo str_repeat('-', 50) . "\n";

        $A = [1, 2, 3, 4];
        $B = [5, 6, 7, 8];

        $AB = CoralMedia\LinearAlgebra::matmul($A, $B, 2, 2, 2);
        $BA = CoralMedia\LinearAlgebra::matmul($B, $A, 2, 2, 2);

        echo "  A × B = [" . implode(', ', $AB) . "]\n";
        echo "  B × A = [" . implode(', ', $BA) . "]\n";

        $different = false;
        for ($i = 0; $i < count($AB); $i++) {
            if (abs($AB[$i] - $BA[$i]) > 0.0001) {
                $different = true;
                break;
            }
        }

        if ($different) {
            echo "  ✓ A×B ≠ B×A (matrix multiplication is not commutative)\n";
            $this->passed++;
        } else {
            echo "  ✗ Unexpected: A×B = B×A\n";
            $this->failed++;
        }

        echo "\n";
    }

    private function testAssociativeProperty(): void
    {
        echo "Test 10: Associative Property ((A×B)×C = A×(B×C))\n";
        echo str_repeat('-', 50) . "\n";

        $A = [1, 2, 3, 4];      // 2×2
        $B = [5, 6, 7, 8];      // 2×2
        $C = [9, 10, 11, 12];   // 2×2

        // (A × B) × C
        $AB = CoralMedia\LinearAlgebra::matmul($A, $B, 2, 2, 2);
        $AB_C = CoralMedia\LinearAlgebra::matmul($AB, $C, 2, 2, 2);

        // A × (B × C)
        $BC = CoralMedia\LinearAlgebra::matmul($B, $C, 2, 2, 2);
        $A_BC = CoralMedia\LinearAlgebra::matmul($A, $BC, 2, 2, 2);

        echo "  (A×B)×C = [" . implode(', ', $AB_C) . "]\n";
        echo "  A×(B×C) = [" . implode(', ', $A_BC) . "]\n";

        $equal = true;
        for ($i = 0; $i < count($AB_C); $i++) {
            if (abs($AB_C[$i] - $A_BC[$i]) > 0.0001) {
                $equal = false;
                break;
            }
        }

        if ($equal) {
            echo "  ✓ (A×B)×C = A×(B×C) (associative property holds)\n";
            $this->passed++;
        } else {
            echo "  ✗ Associative property failed\n";
            $this->failed++;
        }

        echo "\n";
    }

    private function assertMatmul(
        array $a,
        array $b,
        int $m,
        int $n,
        int $k,
        array $expected,
        string $description,
        bool $transposeA = false,
        bool $transposeB = false,
        float $epsilon = 0.0001
    ): void {
        try {
            $actual = CoralMedia\LinearAlgebra::matmul($a, $b, $m, $n, $k, $transposeA, $transposeB);

            $match = true;
            $maxDiff = 0;

            if (count($actual) !== count($expected)) {
                $match = false;
            } else {
                for ($i = 0; $i < count($expected); $i++) {
                    $diff = abs($actual[$i] - $expected[$i]);
                    $maxDiff = max($maxDiff, $diff);
                    if ($diff > $epsilon) {
                        $match = false;
                        break;
                    }
                }
            }

            if ($match) {
                $this->passed++;
                echo sprintf("  ✓ %s\n", $description);
                if ($this->verbose) {
                    echo "    Result: [" . implode(', ', array_map(fn($x) => sprintf('%.2f', $x), $actual)) . "]\n";
                }
            } else {
                $this->failed++;
                $this->errors[] = [
                    'test' => $description,
                    'expected' => $expected,
                    'actual' => $actual,
                    'maxDiff' => $maxDiff,
                ];
                echo sprintf("  ✗ %s\n", $description);
                echo "    Expected: [" . implode(', ', $expected) . "]\n";
                echo "    Got:      [" . implode(', ', $actual) . "]\n";
                echo sprintf("    Max diff: %.6f\n", $maxDiff);
            }
        } catch (Exception $e) {
            $this->failed++;
            $this->errors[] = [
                'test' => $description,
                'expected' => $expected,
                'actual' => 'ERROR: ' . $e->getMessage(),
            ];
            echo sprintf("  ✗ %s\n", $description);
            echo sprintf("    ERROR: %s\n", $e->getMessage());
        }
    }

    private function printSummary(): void
    {
        $total = $this->passed + $this->failed;

        echo "=== Test Summary ===\n";
        echo sprintf("Total tests:  %d\n", $total);
        echo sprintf("✓ Passed:     %d (%.1f%%)\n",
            $this->passed,
            ($total > 0 ? ($this->passed / $total) * 100 : 0)
        );

        if ($this->failed > 0) {
            echo sprintf("✗ Failed:     %d (%.1f%%)\n",
                $this->failed,
                ($this->failed / $total) * 100
            );
        }

        if ($this->failed === 0) {
            echo "\n✅ All tests PASSED!\n";
        } else {
            echo "\n⚠️  Some tests FAILED\n";
        }
    }

    public function runBenchmark(): void
    {
        echo "=== Matrix Multiplication Performance Benchmark ===\n\n";

        $configs = [
            // [m, n, k, description]
            [10, 10, 10, "Small square (10×10)"],
            [50, 50, 50, "Medium square (50×50)"],
            [100, 100, 100, "Large square (100×100)"],
            [500, 500, 500, "XL square (500×500)"],
            [100, 1000, 100, "Tall × Wide (100×1000×100)"],
            [1000, 100, 100, "Wide × Tall (1000×100×100)"],
            [100, 784, 128, "Neural net layer (100×784×128)"],
        ];

        foreach ($configs as [$m, $n, $k, $desc]) {
            echo "{$desc}\n";
            echo str_repeat('-', 50) . "\n";

            // Generate random matrices
            $a = [];
            $b = [];

            for ($i = 0; $i < $m * $n; $i++) {
                $a[] = mt_rand(1, 100) / 10.0;
            }

            for ($i = 0; $i < $n * $k; $i++) {
                $b[] = mt_rand(1, 100) / 10.0;
            }

            // Warmup
            CoralMedia\LinearAlgebra::matmul($a, $b, $m, $n, $k);

            // Benchmark
            $iterations = max(1, intval(1000 / max($m, $n, $k)));
            $startTime = microtime(true);
            $startMemory = memory_get_usage();

            for ($i = 0; $i < $iterations; $i++) {
                $result = CoralMedia\LinearAlgebra::matmul($a, $b, $m, $n, $k);
            }

            $duration = microtime(true) - $startTime;
            $memoryUsed = memory_get_usage() - $startMemory;

            $avgTime = ($duration / $iterations) * 1000;
            $flops = 2.0 * $m * $n * $k; // 2 operations per multiply-add
            $gflops = ($flops * $iterations / $duration) / 1e9;

            echo sprintf("  Matrix dims:     (%d×%d) × (%d×%d) = (%d×%d)\n", $m, $n, $n, $k, $m, $k);
            echo sprintf("  Iterations:      %d\n", $iterations);
            echo sprintf("  Total time:      %.3f sec\n", $duration);
            echo sprintf("  Avg per call:    %.3f ms\n", $avgTime);
            echo sprintf("  FLOPs per call:  %s\n", number_format($flops, 0));
            echo sprintf("  Performance:     %.3f GFLOPS\n", $gflops);
            echo sprintf("  Memory used:     %s\n", $this->formatBytes($memoryUsed));
            echo "\n";
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return round($bytes / 1048576, 2) . ' MB';
        }
    }
}

// Parse command line arguments
$options = getopt('vbh', ['verbose', 'benchmark', 'help']);

if (isset($options['h']) || isset($options['help'])) {
    echo "Usage: php test_matmul.php [options]\n\n";
    echo "Options:\n";
    echo "  -v, --verbose     Show detailed test output\n";
    echo "  -b, --benchmark   Run performance benchmark\n";
    echo "  -h, --help        Show this help message\n\n";
    echo "Examples:\n";
    echo "  php test_matmul.php           # Run test suite\n";
    echo "  php test_matmul.php -v        # Verbose output\n";
    echo "  php test_matmul.php -b        # Performance benchmark\n";
    exit(0);
}

$verbose = isset($options['v']) || isset($options['verbose']);
$benchmark = isset($options['b']) || isset($options['benchmark']);

$runner = new MatmulTestRunner($verbose);

if ($benchmark) {
    $runner->runBenchmark();
} else {
    $runner->runTests();
}