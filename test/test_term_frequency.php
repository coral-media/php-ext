#!/usr/bin/env php
<?php

/**
 * Term Frequency Test Suite
 *
 * Tests term frequency extraction with various options
 */

use CoralMedia\Text;

class TermFrequencyTestRunner
{
    private $verbose = false;
    private $passed = 0;
    private $failed = 0;

    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function runTests(): void
    {
        echo "=== CoralMedia Term Frequency Test Suite ===\n\n";

        $this->testBasicCounting();
        $this->testNormalization();
        $this->testLowercaseOption();
        $this->testDiacriticRemoval();
        $this->testCombinedOptions();
        $this->testMultilingual();
        $this->testStemming();
        $this->testEdgeCases();

        $this->printSummary();
    }

    private function testBasicCounting(): void
    {
        echo "Test 1: Basic Term Counting\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "hello world",
                ["hello" => 1, "world" => 1],
                "Two unique words"
            ],
            [
                "hello world hello",
                ["hello" => 2, "world" => 1],
                "Repeated word"
            ],
            [
                "the quick brown fox jumps over the lazy dog",
                ["the" => 2, "quick" => 1, "brown" => 1, "fox" => 1, "jumps" => 1, "over" => 1, "lazy" => 1, "dog" => 1],
                "Multiple words with repetition"
            ],
        ];

        foreach ($tests as [$text, $expected, $desc]) {
            $this->assertTermFrequency($text, [], $expected, $desc);
        }
        echo "\n";
    }

    private function testNormalization(): void
    {
        echo "Test 2: Normalized Frequencies\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "hello world hello",
                ["normalize" => true],
                ["hello" => 0.666666666, "world" => 0.333333333],
                "Normalized to sum = 1.0",
                0.0001  // tolerance for float comparison
            ],
            [
                "a b c a",
                ["normalize" => true],
                ["a" => 0.5, "b" => 0.25, "c" => 0.25],
                "Three terms normalized",
                0.0001
            ],
        ];

        foreach ($tests as $test) {
            [$text, $options, $expected, $desc] = $test;
            $tolerance = $test[4] ?? 0;
            $this->assertTermFrequency($text, $options, $expected, $desc, $tolerance);
        }
        echo "\n";
    }

    private function testLowercaseOption(): void
    {
        echo "Test 3: Lowercase Option\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "Hello WORLD hello",
                ["lowercase" => true],
                ["hello" => 2, "world" => 1],
                "Lowercase enabled (default)"
            ],
            [
                "Hello WORLD hello",
                ["lowercase" => false],
                ["Hello" => 1, "WORLD" => 1, "hello" => 1],
                "Lowercase disabled"
            ],
        ];

        foreach ($tests as [$text, $options, $expected, $desc]) {
            $this->assertTermFrequency($text, $options, $expected, $desc);
        }
        echo "\n";
    }

    private function testDiacriticRemoval(): void
    {
        echo "Test 4: Diacritic Removal\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "café café coffee",
                ["remove_diacritics" => true],
                ["cafe" => 2, "coffee" => 1],
                "French accents removed"
            ],
            [
                "naïve naive",
                ["remove_diacritics" => true],
                ["naive" => 2],
                "Diaeresis removed"
            ],
            [
                "café café coffee",
                ["remove_diacritics" => false],
                ["café" => 2, "coffee" => 1],
                "Diacritics preserved"
            ],
        ];

        foreach ($tests as [$text, $options, $expected, $desc]) {
            $this->assertTermFrequency($text, $options, $expected, $desc);
        }
        echo "\n";
    }

    private function testCombinedOptions(): void
    {
        echo "Test 5: Combined Options\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "Café CAFÉ coffee",
                ["lowercase" => true, "remove_diacritics" => true],
                ["cafe" => 2, "coffee" => 1],
                "Lowercase + diacritic removal"
            ],
            [
                "Café CAFÉ coffee",
                ["lowercase" => true, "remove_diacritics" => true, "normalize" => true],
                ["cafe" => 0.666666666, "coffee" => 0.333333333],
                "All options combined",
                0.0001
            ],
        ];

        foreach ($tests as $test) {
            [$text, $options, $expected, $desc] = $test;
            $tolerance = $test[4] ?? 0;
            $this->assertTermFrequency($text, $options, $expected, $desc, $tolerance);
        }
        echo "\n";
    }

    private function testMultilingual(): void
    {
        echo "Test 6: Multilingual Text\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "私は学生です私は",
                ["locale" => "ja_JP"],
                ["私" => 2, "は" => 2, "学生" => 1, "です" => 1],
                "Japanese tokenization"
            ],
            [
                "hello world สวัสดี",
                ["locale" => "en_US"],
                ["hello" => 1, "world" => 1, "สวัสดี" => 1],
                "Mixed language"
            ],
        ];

        foreach ($tests as [$text, $options, $expected, $desc]) {
            $this->assertTermFrequency($text, $options, $expected, $desc);
        }
        echo "\n";
    }

    private function testStemming(): void
    {
        echo "Test 7: Stemming\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "running runs runner",
                ["stem" => true],
                ["run" => 2, "runner" => 1],
                "English stemming groups related words"
            ],
            [
                "running runs runner",
                ["stem" => false],
                ["running" => 1, "runs" => 1, "runner" => 1],
                "Without stemming treats as separate words"
            ],
            [
                "running RUNNING Running",
                ["stem" => true, "lowercase" => true],
                ["run" => 3],
                "Stemming with lowercase"
            ],
            [
                "stemming stemmed stems",
                ["stem" => true],
                ["stem" => 3],
                "Multiple forms stem to same root"
            ],
        ];

        foreach ($tests as [$text, $options, $expected, $desc]) {
            $this->assertTermFrequency($text, $options, $expected, $desc);
        }
        echo "\n";
    }

    private function testEdgeCases(): void
    {
        echo "Test 8: Edge Cases\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "",
                [],
                [],
                "Empty string"
            ],
            [
                "   ",
                [],
                [],
                "Only whitespace"
            ],
            [
                "hello",
                [],
                ["hello" => 1],
                "Single word"
            ],
            [
                "hello hello hello",
                [],
                ["hello" => 3],
                "Same word repeated"
            ],
        ];

        foreach ($tests as [$text, $options, $expected, $desc]) {
            $this->assertTermFrequency($text, $options, $expected, $desc);
        }
        echo "\n";
    }

    private function assertTermFrequency(
        string $text,
        array $options,
        array $expected,
        string $desc,
        float $tolerance = 0
    ): void {
        try {
            $actual = Text::termFrequency($text, $options);

            // For float comparisons, use tolerance
            if ($tolerance > 0) {
                $match = $this->arrayFloatEquals($expected, $actual, $tolerance);
            } else {
                $match = ($actual === $expected);
            }

            if ($match) {
                if ($this->verbose) {
                    echo "  ✓ {$desc}\n";
                    echo "    Input:    '{$text}'\n";
                    echo "    Options:  " . json_encode($options) . "\n";
                    echo "    Output:   " . json_encode($actual) . "\n";
                } else {
                    echo "  ✓ {$desc}\n";
                }
                $this->passed++;
            } else {
                echo "  ✗ {$desc}\n";
                echo "    Input:    '{$text}'\n";
                echo "    Options:  " . json_encode($options) . "\n";
                echo "    Expected: " . json_encode($expected) . "\n";
                echo "    Got:      " . json_encode($actual) . "\n";
                $this->failed++;
            }
        } catch (Exception $e) {
            echo "  ✗ {$desc}: ERROR - {$e->getMessage()}\n";
            $this->failed++;
        }
    }

    private function arrayFloatEquals(array $expected, array $actual, float $tolerance): bool
    {
        if (count($expected) !== count($actual)) {
            return false;
        }

        foreach ($expected as $key => $value) {
            if (!isset($actual[$key])) {
                return false;
            }
            if (abs($value - $actual[$key]) > $tolerance) {
                return false;
            }
        }

        return true;
    }

    private function printSummary(): void
    {
        $total = $this->passed + $this->failed;
        echo "\n=== Test Summary ===\n";
        echo sprintf("Total:  %d tests\n", $total);
        echo sprintf("✓ Passed: %d (%.1f%%)\n", $this->passed, ($total > 0 ? ($this->passed / $total) * 100 : 0));
        echo sprintf("✗ Failed: %d (%.1f%%)\n", $this->failed, ($total > 0 ? ($this->failed / $total) * 100 : 0));

        if ($this->failed === 0) {
            echo "\n✅ All tests PASSED!\n";
        } else {
            echo "\n⚠️  Some tests FAILED\n";
        }
    }
}

// Run tests
$verbose = in_array('-v', $argv) || in_array('--verbose', $argv);
$runner = new TermFrequencyTestRunner($verbose);
$runner->runTests();
