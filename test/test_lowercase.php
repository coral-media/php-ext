#!/usr/bin/env php
<?php

/**
 * ICU Lowercase Test Suite
 *
 * Tests locale-aware lowercase conversion
 */

use CoralMedia\Text;

class LowercaseTestRunner
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
        echo "=== CoralMedia ICU Lowercase Test Suite ===\n\n";

        $this->testBasicEnglish();
        $this->testUnicode();
        $this->testTurkishLocale();
        $this->testGreek();
        $this->testCyrillic();
        $this->testGerman();
        $this->testEdgeCases();

        $this->printSummary();
    }

    private function testBasicEnglish(): void
    {
        echo "Test 1: Basic English Lowercase\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["HELLO WORLD", "hello world", "Simple uppercase"],
            ["Hello World", "hello world", "Mixed case"],
            ["hello world", "hello world", "Already lowercase"],
            ["QUICK BROWN FOX", "quick brown fox", "Multiple words"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertLowercase($input, $expected, $desc, "en_US");
        }
        echo "\n";
    }

    private function testUnicode(): void
    {
        echo "Test 2: Unicode Characters\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["CAFÉ", "café", "French accents"],
            ["NAÏVE", "naïve", "Diaeresis"],
            ["BJÖRK", "björk", "Nordic characters"],
            ["ZÜRICH", "zürich", "German umlauts"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertLowercase($input, $expected, $desc, "en_US");
        }
        echo "\n";
    }

    private function testTurkishLocale(): void
    {
        echo "Test 3: Turkish Locale-Specific Case Mapping\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["İSTANBUL", "istanbul", "Turkish dotted I (tr_TR)", "tr_TR"],
            ["ISTANBUL", "ıstanbul", "Turkish dotless I (tr_TR)", "tr_TR"],
            ["İstanbul", "istanbul", "Mixed case with İ (tr_TR)", "tr_TR"],
        ];

        foreach ($tests as [$input, $expected, $desc, $locale]) {
            $this->assertLowercase($input, $expected, $desc, $locale);
        }
        echo "\n";
    }

    private function testGreek(): void
    {
        echo "Test 4: Greek Characters\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["ΑΘΗΝΑ", "αθηνα", "Greek capital letters"],
            ["ΕΛΛΆΔΑ", "ελλάδα", "Greek with accents"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertLowercase($input, $expected, $desc, "el_GR");
        }
        echo "\n";
    }

    private function testCyrillic(): void
    {
        echo "Test 5: Cyrillic Characters\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["МОСКВА", "москва", "Russian capitals"],
            ["САНКТ-ПЕТЕРБУРГ", "санкт-петербург", "Russian with hyphen"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertLowercase($input, $expected, $desc, "ru_RU");
        }
        echo "\n";
    }

    private function testGerman(): void
    {
        echo "Test 6: German Special Cases\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["STRASSE", "strasse", "German SS"],
            ["MÜNCHEN", "münchen", "German umlaut"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertLowercase($input, $expected, $desc, "de_DE");
        }
        echo "\n";
    }

    private function testEdgeCases(): void
    {
        echo "Test 7: Edge Cases\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["", "", "Empty string"],
            ["   ", "   ", "Only whitespace"],
            ["123", "123", "Numbers only"],
            ["!@#$%", "!@#$%", "Punctuation only"],
            ["Test123", "test123", "Alphanumeric"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertLowercase($input, $expected, $desc, "en_US");
        }
        echo "\n";
    }

    private function assertLowercase(string $input, string $expected, string $desc, string $locale): void
    {
        try {
            $actual = Text::lowercase($input, $locale);

            if ($actual === $expected) {
                if ($this->verbose) {
                    echo "  ✓ {$desc}\n";
                    echo "    Input:    '{$input}'\n";
                    echo "    Output:   '{$actual}'\n";
                    echo "    Locale:   {$locale}\n";
                } else {
                    echo "  ✓ {$desc}\n";
                }
                $this->passed++;
            } else {
                echo "  ✗ {$desc}\n";
                echo "    Input:    '{$input}'\n";
                echo "    Expected: '{$expected}'\n";
                echo "    Got:      '{$actual}'\n";
                echo "    Locale:   {$locale}\n";
                $this->failed++;
            }
        } catch (Exception $e) {
            echo "  ✗ {$desc}: ERROR - {$e->getMessage()}\n";
            $this->failed++;
        }
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
$runner = new LowercaseTestRunner($verbose);
$runner->runTests();
