#!/usr/bin/env php
<?php

/**
 * ICU Tokenizer Test Suite
 *
 * Tests word breaking and sentence breaking across multiple languages
 */

use CoralMedia\Text;

class TokenizerTestRunner
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
        echo "=== CoralMedia ICU Tokenizer Test Suite ===\n\n";

        $this->testEnglishWordBreak();
        $this->testJapaneseWordBreak();
        $this->testThaiWordBreak();
        $this->testChineseWordBreak();
        $this->testPunctuationHandling();
        $this->testEmptyStrings();
        $this->testSingleWords();
        $this->testEnglishSentenceBreak();
        $this->testMultilingualSentenceBreak();
        $this->testEdgeCases();

        $this->printSummary();
    }

    private function testEnglishWordBreak(): void
    {
        echo "Test 1: English Word Breaking\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "Hello world",
                ["Hello", "world"],
                "Simple two words"
            ],
            [
                "The quick brown fox jumps over the lazy dog",
                ["The", "quick", "brown", "fox", "jumps", "over", "the", "lazy", "dog"],
                "Classic pangram"
            ],
            [
                "I'm running, aren't you?",
                ["I'm", "running", "aren't", "you"],
                "Contractions and punctuation"
            ],
            [
                "Cost is \$5.99 USD",
                ["Cost", "is", "5.99", "USD"],
                "Numbers and currency"
            ],
        ];

        foreach ($tests as [$text, $expected, $desc]) {
            $this->assertWordBreak($text, $expected, $desc, "en_US");
        }
        echo "\n";
    }

    private function testJapaneseWordBreak(): void
    {
        echo "Test 2: Japanese Word Breaking\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "私は学生です",
                ["私", "は", "学生", "です"],
                "Simple Japanese sentence"
            ],
            [
                "東京に行きます",
                ["東京", "に", "行き", "ます"],
                "Japanese with location"
            ],
        ];

        foreach ($tests as [$text, $expected, $desc]) {
            $this->assertWordBreak($text, $expected, $desc, "ja_JP");
        }
        echo "\n";
    }

    private function testThaiWordBreak(): void
    {
        echo "Test 3: Thai Word Breaking\n";
        echo str_repeat('-', 50) . "\n";

        // Thai has no spaces between words - ICU dictionary-based breaking is essential
        $tests = [
            [
                "สวัสดีครับ",
                ["สวัสดี", "ครับ"],
                "Thai greeting (no spaces)"
            ],
            [
                "ฉันชอบกินข้าว",
                ["ฉัน", "ชอบ", "กิน", "ข้าว"],
                "Thai sentence (no spaces)"
            ],
        ];

        foreach ($tests as [$text, $expected, $desc]) {
            $this->assertWordBreak($text, $expected, $desc, "th_TH");
        }
        echo "\n";
    }

    private function testChineseWordBreak(): void
    {
        echo "Test 4: Chinese Word Breaking\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "我爱中国",
                ["我", "爱", "中国"],
                "Simple Chinese sentence"
            ],
        ];

        foreach ($tests as [$text, $expected, $desc]) {
            $this->assertWordBreak($text, $expected, $desc, "zh_CN");
        }
        echo "\n";
    }

    private function testPunctuationHandling(): void
    {
        echo "Test 5: Punctuation Handling\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "Hello, world!",
                ["Hello", "world"],
                "Comma and exclamation (should be filtered)"
            ],
            [
                "one-two-three",
                ['one' ,'two', 'three'],
                "Hyphenated word (kept together)"
            ],
            [
                "test@example.com",
                ["test", "example.com"],
                "Email address"
            ],
        ];

        foreach ($tests as [$text, $expected, $desc]) {
            $this->assertWordBreak($text, $expected, $desc, "en_US");
        }
        echo "\n";
    }

    private function testEmptyStrings(): void
    {
        echo "Test 6: Empty and Whitespace Strings\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["", [], "Empty string"],
            ["   ", [], "Only whitespace"],
            ["\n\t", [], "Only newlines and tabs"],
        ];

        foreach ($tests as [$text, $expected, $desc]) {
            $this->assertWordBreak($text, $expected, $desc, "en_US");
        }
        echo "\n";
    }

    private function testSingleWords(): void
    {
        echo "Test 7: Single Words\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            ["hello", ["hello"], "Single word"],
            ["  word  ", ["word"], "Single word with whitespace"],
        ];

        foreach ($tests as [$text, $expected, $desc]) {
            $this->assertWordBreak($text, $expected, $desc, "en_US");
        }
        echo "\n";
    }

    private function testEnglishSentenceBreak(): void
    {
        echo "Test 8: English Sentence Breaking\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "Hello. World.",
                ["Hello. ", "World."],
                "Two simple sentences"
            ],
            [
                "This is a test. This is only a test!",
                ["This is a test. ", "This is only a test!"],
                "Sentences with different punctuation"
            ],
            [
                "Dr. Smith went to Washington D.C. today.",
                ["Dr. Smith went to Washington D.C. today."],
                "Abbreviations (should not break)"
            ],
        ];

        foreach ($tests as [$text, $expected, $desc]) {
            $this->assertSentenceBreak($text, $expected, $desc, "en_US");
        }
        echo "\n";
    }

    private function testMultilingualSentenceBreak(): void
    {
        echo "Test 9: Multilingual Sentence Breaking\n";
        echo str_repeat('-', 50) . "\n";

        $tests = [
            [
                "こんにちは。元気ですか。",
                ["こんにちは。", "元気ですか。"],
                "Japanese sentences",
                "ja_JP"
            ],
        ];

        foreach ($tests as [$text, $expected, $desc, $locale]) {
            $this->assertSentenceBreak($text, $expected, $desc, $locale);
        }
        echo "\n";
    }

    private function testEdgeCases(): void
    {
        echo "Test 10: Edge Cases\n";
        echo str_repeat('-', 50) . "\n";

        // Test invalid locale handling
        try {
            $result = Text::wordBreak("test", "invalid_locale");
            echo "  ✗ Invalid locale should throw error\n";
            $this->failed++;
        } catch (Exception $e) {
            echo "  ✓ Invalid locale correctly throws error\n";
            $this->passed++;
        }

        echo "\n";
    }

    private function assertWordBreak(string $text, array $expected, string $desc, string $locale): void
    {
        try {
            $actual = Text::wordBreak($text, $locale);

            if ($actual === $expected) {
                if ($this->verbose) {
                    echo "  ✓ {$desc}\n";
                    echo "    Input: '{$text}'\n";
                    echo "    Output: [" . implode(", ", array_map(fn($w) => "'{$w}'", $actual)) . "]\n";
                } else {
                    echo "  ✓ {$desc}\n";
                }
                $this->passed++;
            } else {
                echo "  ✗ {$desc}\n";
                echo "    Input: '{$text}'\n";
                echo "    Expected: [" . implode(", ", array_map(fn($w) => "'{$w}'", $expected)) . "]\n";
                echo "    Got:      [" . implode(", ", array_map(fn($w) => "'{$w}'", $actual)) . "]\n";
                $this->failed++;
            }
        } catch (Exception $e) {
            echo "  ✗ {$desc}: ERROR - {$e->getMessage()}\n";
            $this->failed++;
        }
    }

    private function assertSentenceBreak(string $text, array $expected, string $desc, string $locale): void
    {
        try {
            $actual = Text::sentenceBreak($text, $locale);

            if ($actual === $expected) {
                if ($this->verbose) {
                    echo "  ✓ {$desc}\n";
                } else {
                    echo "  ✓ {$desc}\n";
                }
                $this->passed++;
            } else {
                echo "  ✗ {$desc}\n";
                echo "    Input: '{$text}'\n";
                echo "    Expected: " . json_encode($expected, JSON_UNESCAPED_UNICODE) . "\n";
                echo "    Got:      " . json_encode($actual, JSON_UNESCAPED_UNICODE) . "\n";
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
$runner = new TokenizerTestRunner($verbose);
$runner->runTests();
