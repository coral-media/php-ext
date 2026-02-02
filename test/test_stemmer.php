#!/usr/bin/env php
<?php

/**
 * Snowball Stemmer Test Suite
 * 
 * Validates CoralMedia's Snowball stemmer implementation against
 * reference vocabulary and expected output files.
 */

class StemmerTestRunner
{
    private $testDataDir;
    private $verbose = false;

    public function __construct(string $testDataDir, bool $verbose = false)
    {
        $this->testDataDir = rtrim($testDataDir, '/');
        $this->verbose = $verbose;
    }

    public function runTests(): void
    {
        echo "=== CoralMedia Snowball Stemmer Test Suite ===\n\n";

        // Test English
        $this->testLanguage('english');
        
        // Test Spanish
        $this->testLanguage('spanish');

        echo "\n=== All Tests Complete ===\n";
    }

    private function testLanguage(string $lang): void
    {
        echo "Testing {$lang} stemmer...\n";
        echo str_repeat('-', 50) . "\n";

        $vocabFile = "{$this->testDataDir}/{$lang}_vocabulary.txt";
        $outputFile = "{$this->testDataDir}/{$lang}_output.txt";

        if (!file_exists($vocabFile) || !file_exists($outputFile)) {
            echo "❌ Test data files not found for {$lang}\n\n";
            return;
        }

        $vocabulary = file($vocabFile, FILE_IGNORE_NEW_LINES);
        $expectedOutputs = file($outputFile, FILE_IGNORE_NEW_LINES);

        if (count($vocabulary) !== count($expectedOutputs)) {
            echo "❌ Vocabulary and output file mismatch\n\n";
            return;
        }

        $totalTests = count($vocabulary);
        $passed = 0;
        $failed = 0;
        $errors = [];
        $startTime = microtime(true);

        foreach ($vocabulary as $index => $word) {
            $expected = $expectedOutputs[$index];
            
            try {
                $actual = CoralMedia\Stemmer\Snowball::stem($word, $lang);
                
                if ($actual === $expected) {
                    $passed++;
                    if ($this->verbose && $index < 10) {
                        echo "✓ '{$word}' -> '{$actual}'\n";
                    }
                } else {
                    $failed++;
                    $errors[] = [
                        'word' => $word,
                        'expected' => $expected,
                        'actual' => $actual,
                        'line' => $index + 1
                    ];
                    
                    if ($this->verbose) {
                        echo "✗ '{$word}': expected '{$expected}', got '{$actual}'\n";
                    }
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'word' => $word,
                    'expected' => $expected,
                    'actual' => 'ERROR: ' . $e->getMessage(),
                    'line' => $index + 1
                ];
            }
        }

        $duration = microtime(true) - $startTime;
        $throughput = $totalTests / $duration;

        // Print summary
        echo "\nResults:\n";
        echo sprintf("  Total tests:    %s\n", number_format($totalTests));
        echo sprintf("  ✓ Passed:       %s (%.2f%%)\n", 
            number_format($passed), 
            ($passed / $totalTests) * 100
        );
        
        if ($failed > 0) {
            echo sprintf("  ✗ Failed:       %s (%.2f%%)\n", 
                number_format($failed), 
                ($failed / $totalTests) * 100
            );
        }

        echo sprintf("  Duration:       %.3f seconds\n", $duration);
        echo sprintf("  Throughput:     %s words/sec\n", number_format($throughput, 0));

        // Show first few failures if any
        if ($failed > 0 && !$this->verbose) {
            echo "\nFirst " . min(5, count($errors)) . " failures:\n";
            foreach (array_slice($errors, 0, 5) as $error) {
                echo sprintf("  Line %d: '%s'\n", $error['line'], $error['word']);
                echo sprintf("    Expected: '%s'\n", $error['expected']);
                echo sprintf("    Got:      '%s'\n", $error['actual']);
            }
            
            if (count($errors) > 5) {
                echo sprintf("  ... and %d more failures\n", count($errors) - 5);
            }
        }

        // Overall result
        if ($failed === 0) {
            echo "\n✅ All {$lang} tests PASSED!\n";
        } else {
            echo "\n⚠️  Some {$lang} tests FAILED\n";
        }

        echo "\n";
    }

    public function runQuickTests(): void
    {
        echo "=== Quick Stemmer Tests ===\n\n";

        $quickTests = [
            'english' => [
                ['running', 'run'],
                ['runs', 'run'],
                ['ran', 'ran'],
                ['easily', 'easili'],
                ['fairly', 'fairli'],
                ['electrical', 'electr'],
                ['electricity', 'electr'],
                ['hopping', 'hop'],
                ['hoped', 'hope'],
                ['crying', 'cri'],
                ['cries', 'cri'],
                ['organization', 'organ'],
                ['organizations', 'organ'],
                ['organizing', 'organ'],
            ],
            'spanish' => [
                ['corriendo', 'corr'],
                ['correr', 'corr'],
                ['corrió', 'corr'],
                ['casas', 'cas'],
                ['casa', 'cas'],
                ['comiendo', 'com'],
                ['comida', 'comid'],
                ['bebiendo', 'beb'],
                ['bebida', 'bebid'],
                ['trabajando', 'trabaj'],
                ['trabajador', 'trabaj'],
            ],
        ];

        foreach ($quickTests as $lang => $tests) {
            echo ucfirst($lang) . " quick tests:\n";
            
            $passed = 0;
            $failed = 0;

            foreach ($tests as [$word, $expected]) {
                $actual = CoralMedia\Stemmer\Snowball::stem($word, $lang);
                
                if ($actual === $expected) {
                    echo sprintf("  ✓ '%s' -> '%s'\n", $word, $actual);
                    $passed++;
                } else {
                    echo sprintf("  ✗ '%s': expected '%s', got '%s'\n", 
                        $word, $expected, $actual
                    );
                    $failed++;
                }
            }

            echo sprintf("\n  Result: %d/%d passed\n\n", $passed, count($tests));
        }
    }

    public function runBenchmark(): void
    {
        echo "=== Stemmer Performance Benchmark ===\n\n";

        $vocabFile = "{$this->testDataDir}/english_vocabulary.txt";
        
        if (!file_exists($vocabFile)) {
            echo "❌ Benchmark vocabulary file not found\n";
            return;
        }

        // Load vocabulary
        $vocabulary = file($vocabFile, FILE_IGNORE_NEW_LINES);
        $sampleSize = min(10000, count($vocabulary));
        $sample = array_slice($vocabulary, 0, $sampleSize);

        echo "Stemming {$sampleSize} English words...\n";

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        foreach ($sample as $word) {
            CoralMedia\Stemmer\Snowball::stem($word, 'english');
        }

        $duration = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage() - $startMemory;
        $throughput = $sampleSize / $duration;

        echo "\nBenchmark Results:\n";
        echo sprintf("  Words processed:  %s\n", number_format($sampleSize));
        echo sprintf("  Duration:         %.3f seconds\n", $duration);
        echo sprintf("  Throughput:       %s words/sec\n", number_format($throughput, 0));
        echo sprintf("  Avg per word:     %.3f ms\n", ($duration / $sampleSize) * 1000);
        echo sprintf("  Memory used:      %s\n", $this->formatBytes($memoryUsed));
        echo "\n";
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
$options = getopt('vqbh', ['verbose', 'quick', 'benchmark', 'help']);

if (isset($options['h']) || isset($options['help'])) {
    echo "Usage: php test_stemmer.php [options]\n\n";
    echo "Options:\n";
    echo "  -v, --verbose     Show detailed test output\n";
    echo "  -q, --quick       Run quick sanity tests only\n";
    echo "  -b, --benchmark   Run performance benchmark\n";
    echo "  -h, --help        Show this help message\n\n";
    echo "Examples:\n";
    echo "  php test_stemmer.php              # Run full test suite\n";
    echo "  php test_stemmer.php -q           # Quick tests only\n";
    echo "  php test_stemmer.php -b           # Performance benchmark\n";
    echo "  php test_stemmer.php -v           # Verbose output\n";
    exit(0);
}

$verbose = isset($options['v']) || isset($options['verbose']);
$quick = isset($options['q']) || isset($options['quick']);
$benchmark = isset($options['b']) || isset($options['benchmark']);

// Determine test data directory
$scriptDir = __DIR__;
$testDataDir = $scriptDir . '/data/stemmer';

$runner = new StemmerTestRunner($testDataDir, $verbose);

if ($quick) {
    $runner->runQuickTests();
} elseif ($benchmark) {
    $runner->runBenchmark();
} else {
    // Run full test suite
    $runner->runTests();
}