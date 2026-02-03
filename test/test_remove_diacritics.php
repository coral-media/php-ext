#!/usr/bin/env php
<?php

/**
 * ICU Remove Diacritics Test Suite
 *
 * Tests removal of diacritical marks (accents) from text
 */

use CoralMedia\Text;

class RemoveDiacriticsTestRunner
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
        echo "=== CoralMedia ICU Remove Diacritics Test Suite ===\n\n";

        $this->testFrenchAccents();
        $this->testGermanUmlauts();
        $this->testSpanishAccents();
        $this->testPortugueseAccents();
        $this->testNordicCharacters();
        $this->testVietnamese();
        $this->testMixedText();
        $this->testEdgeCases();

        $this->printSummary();
    }

    private function testFrenchAccents(): void
    {
        echo "Test 1: French Accents\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["café", "cafe", "Acute accent (é)"],
            ["crème", "creme", "Grave accent (è)"],
            ["naïve", "naive", "Diaeresis (ï)"],
            ["hôtel", "hotel", "Circumflex (ô)"],
            ["français", "francais", "Cedilla (ç)"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertRemoveDiacritics($input, $expected, $desc);
        }
        echo "\n";
    }

    private function testGermanUmlauts(): void
    {
        echo "Test 2: German Umlauts\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["Zürich", "Zurich", "U-umlaut"],
            ["München", "Munchen", "U-umlaut in word"],
            ["Köln", "Koln", "O-umlaut"],
            ["Ärzte", "Arzte", "A-umlaut"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertRemoveDiacritics($input, $expected, $desc);
        }
        echo "\n";
    }

    private function testSpanishAccents(): void
    {
        echo "Test 3: Spanish Accents\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["español", "espanol", "Tilde over n"],
            ["José", "Jose", "Acute accent"],
            ["Málaga", "Malaga", "Acute on a"],
            ["Ángel", "Angel", "Acute on A"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertRemoveDiacritics($input, $expected, $desc);
        }
        echo "\n";
    }

    private function testPortugueseAccents(): void
    {
        echo "Test 4: Portuguese Accents\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["São Paulo", "Sao Paulo", "Tilde over a"],
            ["João", "Joao", "Tilde over a in name"],
            ["pão", "pao", "Tilde in word"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertRemoveDiacritics($input, $expected, $desc);
        }
        echo "\n";
    }

    private function testNordicCharacters(): void
    {
        echo "Test 5: Nordic Characters\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["Björk", "Bjork", "Swedish o-umlaut"],
            ["Åse", "Ase", "Ring above (å)"],
            ["Øyvind", "Øyvind", "Ø is not a diacritic"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertRemoveDiacritics($input, $expected, $desc);
        }
        echo "\n";
    }

    private function testVietnamese(): void
    {
        echo "Test 6: Vietnamese Accents\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["Việt Nam", "Viet Nam", "Vietnamese complex diacritics"],
            ["phở", "pho", "Horn and tone mark"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertRemoveDiacritics($input, $expected, $desc);
        }
        echo "\n";
    }

    private function testMixedText(): void
    {
        echo "Test 7: Mixed Text\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["Café français", "Cafe francais", "Multiple French accents"],
            ["Résumé", "Resume", "Multiple accents same word"],
            ["El Niño", "El Nino", "Spanish with space"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertRemoveDiacritics($input, $expected, $desc);
        }
        echo "\n";
    }

    private function testEdgeCases(): void
    {
        echo "Test 8: Edge Cases\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["", "", "Empty string"],
            ["Hello", "Hello", "No diacritics"],
            ["123", "123", "Numbers only"],
            ["!@#", "!@#", "Punctuation only"],
            ["café123", "cafe123", "Mixed alphanumeric"],
        ];

        foreach ($tests as [$input, $expected, $desc]) {
            $this->assertRemoveDiacritics($input, $expected, $desc);
        }
        echo "\n";
    }

    private function assertRemoveDiacritics(string $input, string $expected, string $desc): void
    {
        try {
            $actual = Text::removeDiacritics($input);

            if ($actual === $expected) {
                if ($this->verbose) {
                    echo "  ✓ {$desc}\n";
                    echo "    Input:    '{$input}'\n";
                    echo "    Output:   '{$actual}'\n";
                } else {
                    echo "  ✓ {$desc}\n";
                }
                $this->passed++;
            } else {
                echo "  ✗ {$desc}\n";
                echo "    Input:    '{$input}'\n";
                echo "    Expected: '{$expected}'\n";
                echo "    Got:      '{$actual}'\n";
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
$runner = new RemoveDiacriticsTestRunner($verbose);
$runner->runTests();
