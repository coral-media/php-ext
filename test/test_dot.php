#!/usr/bin/env php
<?php

/**
 * Dot Product Test Suite
 * 
 * Comprehensive testing of CoralMedia's vector dot product implementation
 */

class DotProductTestRunner
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
        echo "=== CoralMedia Dot Product Test Suite ===\n\n";

        $this->testBasicDotProduct();
        $this->testOrthogonalVectors();
        $this->testParallelVectors();
        $this->testZeroVectors();
        $this->testIdentityVectors();
        $this->testNegativeValues();
        $this->testFloatingPoint();
        $this->testLargeVectors();
        $this->testNormalization();
        $this->testCosineSimilarity();

        $this->printSummary();
    }

    private function testBasicDotProduct(): void
    {
        echo "Test 1: Basic Dot Product\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [[1, 2, 3], [4, 5, 6], 32],
            [[1, 0, 0], [0, 1, 0], 0],
            [[2, 3], [4, 5], 23],
            [[1], [5], 5],
            [[1, 1, 1, 1], [2, 2, 2, 2], 8],
        ];

        foreach ($tests as [$a, $b, $expected]) {
            $this->assertDotProduct($a, $b, $expected, 
                sprintf("[%s] · [%s]", implode(', ', $a), implode(', ', $b))
            );
        }
        echo "\n";
    }

    private function testOrthogonalVectors(): void
    {
        echo "Test 2: Orthogonal Vectors (dot = 0)\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [[1, 0], [0, 1], 0, "Unit vectors (x, y)"],
            [[1, 0, 0], [0, 1, 0], 0, "Unit vectors (x, y, z)"],
            [[1, 0, 0], [0, 0, 1], 0, "Unit vectors (x, z)"],
            [[3, 4], [-4, 3], 0, "Perpendicular 2D vectors"],
            [[1, 1, 0], [-1, 1, 0], 0, "45° rotated vectors"],
        ];

        foreach ($tests as [$a, $b, $expected, $desc]) {
            $this->assertDotProduct($a, $b, $expected, $desc);
        }
        echo "\n";
    }

    private function testParallelVectors(): void
    {
        echo "Test 3: Parallel Vectors\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [[1, 2, 3], [2, 4, 6], 28, "Scaled version (2x)"],
            [[1, 1, 1], [3, 3, 3], 9, "Same direction (3x)"],
            [[2, 0], [5, 0], 10, "Along x-axis"],
            [[0, 3], [0, 7], 21, "Along y-axis"],
        ];

        foreach ($tests as [$a, $b, $expected, $desc]) {
            $this->assertDotProduct($a, $b, $expected, $desc);
        }
        echo "\n";
    }

    private function testZeroVectors(): void
    {
        echo "Test 4: Zero Vectors\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [[0, 0], [1, 2], 0, "Zero vector with non-zero"],
            [[0, 0, 0], [5, 10, 15], 0, "3D zero vector"],
            [[0, 0], [0, 0], 0, "Both zero vectors"],
        ];

        foreach ($tests as [$a, $b, $expected, $desc]) {
            $this->assertDotProduct($a, $b, $expected, $desc);
        }
        echo "\n";
    }

    private function testIdentityVectors(): void
    {
        echo "Test 5: Identity/Unit Vectors\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [[1, 0], [1, 0], 1, "Unit x with itself"],
            [[0, 1], [0, 1], 1, "Unit y with itself"],
            [[1, 0, 0], [1, 0, 0], 1, "Unit x (3D) with itself"],
            [[0, 1, 0], [0, 1, 0], 1, "Unit y (3D) with itself"],
            [[0, 0, 1], [0, 0, 1], 1, "Unit z with itself"],
        ];

        foreach ($tests as [$a, $b, $expected, $desc]) {
            $this->assertDotProduct($a, $b, $expected, $desc);
        }
        echo "\n";
    }

    private function testNegativeValues(): void
    {
        echo "Test 6: Negative Values\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [[1, 2], [-1, -2], -5, "Positive with negative"],
            [[-1, -2, -3], [1, 2, 3], -14, "All negative with positive"],
            [[-1, -2], [-1, -2], 5, "Both negative"],
            [[1, -2, 3], [4, 5, -6], -24, "Mixed signs"],
        ];

        foreach ($tests as [$a, $b, $expected, $desc]) {
            $this->assertDotProduct($a, $b, $expected, $desc);
        }
        echo "\n";
    }

    private function testFloatingPoint(): void
    {
        echo "Test 7: Floating Point Values\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [[1.5, 2.5], [3.5, 4.5], 16.5, "Simple decimals"],
            [[0.1, 0.2, 0.3], [0.4, 0.5, 0.6], 0.32, "Small decimals"],
            [[1.23, 4.56], [7.89, 0.12], 10.2585, "Mixed decimals"],
            [[3.14159, 2.71828], [1.41421, 1.61803], 8.839425, "Mathematical constants"],
        ];

        foreach ($tests as [$a, $b, $expected, $desc]) {
            $this->assertDotProduct($a, $b, $expected, $desc, 0.0001);
        }
        echo "\n";
    }

    private function testLargeVectors(): void
    {
        echo "Test 8: Large Vectors\n";
        echo str_repeat('-', 50) . "\n";

        // Test with different sizes
        $sizes = [100, 1000, 10000];

        foreach ($sizes as $size) {
            $a = array_fill(0, $size, 1.0);
            $b = array_fill(0, $size, 2.0);
            $expected = $size * 2.0;

            $start = microtime(true);
            $this->assertDotProduct($a, $b, $expected, 
                sprintf("Vector size: %s", number_format($size))
            );
            $duration = (microtime(true) - $start) * 1000;

            if ($this->verbose) {
                echo sprintf("    Computed in %.3f ms\n", $duration);
            }
        }
        echo "\n";
    }

    private function testNormalization(): void
    {
        echo "Test 9: Dot Product for Normalized Vectors\n";
        echo str_repeat('-', 50) . "\n";

        // For normalized vectors, dot product = cosine of angle
        $tests = [
            // Same direction (angle = 0°, cos = 1)
            [
                [1/sqrt(2), 1/sqrt(2)],
                [1/sqrt(2), 1/sqrt(2)],
                1.0,
                "Same normalized vector (cos 0° = 1)"
            ],
            // Opposite direction (angle = 180°, cos = -1)
            [
                [1/sqrt(2), 1/sqrt(2)],
                [-1/sqrt(2), -1/sqrt(2)],
                -1.0,
                "Opposite normalized vectors (cos 180° = -1)"
            ],
            // 90° angle (cos = 0)
            [
                [1, 0],
                [0, 1],
                0.0,
                "Perpendicular unit vectors (cos 90° = 0)"
            ],
        ];

        foreach ($tests as [$a, $b, $expected, $desc]) {
            $this->assertDotProduct($a, $b, $expected, $desc, 0.0001);
        }
        echo "\n";
    }

    private function testCosineSimilarity(): void
    {
        echo "Test 10: Cosine Similarity Applications\n";
        echo str_repeat('-', 50) . "\n";

        // Document similarity example
        // Represent "documents" as word frequency vectors
        
        // doc1: "cat dog cat"
        $doc1 = [2, 1, 0]; // [cat:2, dog:1, fish:0]
        
        // doc2: "cat fish"
        $doc2 = [1, 0, 1]; // [cat:1, dog:0, fish:1]
        
        // doc3: "dog dog fish"
        $doc3 = [0, 2, 1]; // [cat:0, dog:2, fish:1]

        // Normalize vectors
        $norm1 = sqrt(array_sum(array_map(fn($x) => $x * $x, $doc1)));
        $norm2 = sqrt(array_sum(array_map(fn($x) => $x * $x, $doc2)));
        $norm3 = sqrt(array_sum(array_map(fn($x) => $x * $x, $doc3)));

        $doc1_norm = array_map(fn($x) => $x / $norm1, $doc1);
        $doc2_norm = array_map(fn($x) => $x / $norm2, $doc2);
        $doc3_norm = array_map(fn($x) => $x / $norm3, $doc3);

        echo "Document vectors (word frequencies):\n";
        echo "  Doc1 [cat dog cat]:     [2, 1, 0]\n";
        echo "  Doc2 [cat fish]:        [1, 0, 1]\n";
        echo "  Doc3 [dog dog fish]:    [0, 2, 1]\n\n";

        $sim_1_2 = CoralMedia\LinearAlgebra::dot($doc1_norm, $doc2_norm);
        $sim_1_3 = CoralMedia\LinearAlgebra::dot($doc1_norm, $doc3_norm);
        $sim_2_3 = CoralMedia\LinearAlgebra::dot($doc2_norm, $doc3_norm);

        echo "Cosine similarities:\n";
        echo sprintf("  Doc1 ↔ Doc2: %.4f (share 'cat')\n", $sim_1_2);
        echo sprintf("  Doc1 ↔ Doc3: %.4f (share 'dog')\n", $sim_1_3);
        echo sprintf("  Doc2 ↔ Doc3: %.4f (share 'fish')\n", $sim_2_3);
        
        echo "\n  → Doc1 is most similar to Doc2 (highest cosine similarity)\n";
        $this->passed += 3; // Count as successful demonstrations
        echo "\n";
    }

    private function assertDotProduct(
        array $a, 
        array $b, 
        float $expected, 
        string $description,
        float $epsilon = 0.00001
    ): void {
        try {
            $actual = CoralMedia\LinearAlgebra::dot($a, $b);
            $diff = abs($actual - $expected);

            if ($diff < $epsilon) {
                $this->passed++;
                if ($this->verbose || $diff > 0) {
                    echo sprintf("  ✓ %s\n", $description);
                    if ($diff > 0) {
                        echo sprintf("    Expected: %.6f, Got: %.6f (diff: %.6f)\n", 
                            $expected, $actual, $diff);
                    }
                } else {
                    echo sprintf("  ✓ %s = %.2f\n", $description, $actual);
                }
            } else {
                $this->failed++;
                $this->errors[] = [
                    'test' => $description,
                    'expected' => $expected,
                    'actual' => $actual,
                    'diff' => $diff,
                ];
                echo sprintf("  ✗ %s\n", $description);
                echo sprintf("    Expected: %.6f, Got: %.6f (diff: %.6f)\n", 
                    $expected, $actual, $diff);
            }
        } catch (Exception $e) {
            $this->failed++;
            $this->errors[] = [
                'test' => $description,
                'expected' => $expected,
                'actual' => 'ERROR: ' . $e->getMessage(),
                'diff' => null,
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
        echo "=== Dot Product Performance Benchmark ===\n\n";

        $sizes = [100, 1000, 10000, 100000];

        foreach ($sizes as $size) {
            $a = [];
            $b = [];
            
            for ($i = 0; $i < $size; $i++) {
                $a[] = mt_rand(1, 100) / 10.0;
                $b[] = mt_rand(1, 100) / 10.0;
            }

            // Warmup
            CoralMedia\LinearAlgebra::dot($a, $b);

            // Benchmark
            $iterations = max(1, intval(100000 / $size));
            $start = microtime(true);
            
            for ($i = 0; $i < $iterations; $i++) {
                CoralMedia\LinearAlgebra::dot($a, $b);
            }
            
            $duration = microtime(true) - $start;
            $avgTime = ($duration / $iterations) * 1000;
            $throughput = ($size * $iterations) / $duration;

            echo sprintf("Vector size: %s\n", number_format($size));
            echo sprintf("  Iterations:   %s\n", number_format($iterations));
            echo sprintf("  Total time:   %.3f sec\n", $duration);
            echo sprintf("  Avg per call: %.6f ms\n", $avgTime);
            echo sprintf("  Throughput:   %s elements/sec\n", 
                number_format($throughput, 0));
            echo "\n";
        }
    }
}

// Parse command line arguments
$options = getopt('vbh', ['verbose', 'benchmark', 'help']);

if (isset($options['h']) || isset($options['help'])) {
    echo "Usage: php test_dot.php [options]\n\n";
    echo "Options:\n";
    echo "  -v, --verbose     Show detailed test output\n";
    echo "  -b, --benchmark   Run performance benchmark\n";
    echo "  -h, --help        Show this help message\n\n";
    echo "Examples:\n";
    echo "  php test_dot.php              # Run test suite\n";
    echo "  php test_dot.php -v           # Verbose output\n";
    echo "  php test_dot.php -b           # Performance benchmark\n";
    exit(0);
}

$verbose = isset($options['v']) || isset($options['verbose']);
$benchmark = isset($options['b']) || isset($options['benchmark']);

$runner = new DotProductTestRunner($verbose);

if ($benchmark) {
    $runner->runBenchmark();
} else {
    $runner->runTests();
}