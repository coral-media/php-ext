#!/usr/bin/env php
<?php

/**
 * IDF and TF-IDF Test Suite
 *
 * Tests inverse document frequency and TF-IDF scoring
 */

use CoralMedia\Text;

class IdfTfidfTestRunner
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
        echo "=== CoralMedia IDF and TF-IDF Test Suite ===\n\n";

        $this->testBasicIdf();
        $this->testIdfWithOptions();
        $this->testSmoothIdf();
        $this->testBasicTfidf();
        $this->testTfidfWithOptions();
        $this->testTfidfPipeline();
        $this->testStemming();
        $this->testEdgeCases();

        $this->printSummary();
    }

    private function testBasicIdf(): void
    {
        echo "Test 1: Basic IDF Calculation\n";
        echo str_repeat('-', 50) . "\n";

        $docs = [
            "the cat sat on the mat",
            "the dog sat on the log",
            "cats and dogs"
        ];

        $idf = Text::idf($docs);

        // "the" appears in 2/3 documents
        // Smooth IDF: log((3+1)/(2+1)) + 1 = log(4/3) + 1 ≈ 1.288
        $this->assertFloatEquals($idf["the"], 1.288, 0.01, "IDF for 'the' (appears in 2 docs)");

        // "cat" appears in 1/3 documents
        // Smooth IDF: log((3+1)/(1+1)) + 1 = log(2) + 1 ≈ 1.693
        $this->assertFloatEquals($idf["cat"], 1.693, 0.01, "IDF for 'cat' (appears in 1 doc)");

        // "and" appears in 1/3 documents
        $this->assertFloatEquals($idf["and"], 1.693, 0.01, "IDF for 'and' (appears in 1 doc)");

        echo "\n";
    }

    private function testIdfWithOptions(): void
    {
        echo "Test 2: IDF with Options\n";
        echo str_repeat('-', 50) . "\n";

        // Test lowercase option
        $docs = ["Hello World", "HELLO Universe", "Goodbye World"];
        $idf = Text::idf($docs, ["lowercase" => true]);

        // "hello" should appear in 2 documents when lowercased
        $this->assertTrue(isset($idf["hello"]), "IDF includes lowercased 'hello'");
        $this->assertFalse(isset($idf["Hello"]), "IDF does not include capitalized 'Hello'");

        // Test diacritic removal
        $docs = ["café coffee", "café tea", "coffee tea"];
        $idf = Text::idf($docs, ["remove_diacritics" => true]);

        // "cafe" (from café) should appear in 2 documents
        $this->assertTrue(isset($idf["cafe"]), "IDF includes 'cafe' with diacritics removed");

        echo "\n";
    }

    private function testSmoothIdf(): void
    {
        echo "Test 3: Smooth vs Standard IDF\n";
        echo str_repeat('-', 50) . "\n";

        $docs = ["the cat", "the dog", "the bird"];

        // Smooth IDF (default)
        $smoothIdf = Text::idf($docs, ["smooth" => true]);

        // Standard IDF
        $standardIdf = Text::idf($docs, ["smooth" => false]);

        // Smooth IDF should be different from standard
        $this->assertFloatNotEquals(
            $smoothIdf["the"],
            $standardIdf["the"],
            0.01,
            "Smooth IDF differs from standard IDF"
        );

        // Standard IDF for "the" (appears in all 3 docs): log(3/3) = 0
        $this->assertFloatEquals($standardIdf["the"], 0.0, 0.01, "Standard IDF for term in all docs is ~0");

        // Smooth IDF for "the": log(4/4) + 1 = 1.0
        $this->assertFloatEquals($smoothIdf["the"], 1.0, 0.01, "Smooth IDF prevents zero values");

        echo "\n";
    }

    private function testBasicTfidf(): void
    {
        echo "Test 4: Basic TF-IDF Calculation\n";
        echo str_repeat('-', 50) . "\n";

        $docs = [
            "the cat sat on the mat",
            "the dog sat on the log"
        ];

        $idf = Text::idf($docs);
        $tfidf = Text::tfidf("the cat sat on the mat", $idf);

        // "the" appears 2 times in document, should have TF=2
        // TF-IDF = TF * IDF = 2 * IDF["the"]
        $expectedTfidf = 2 * $idf["the"];
        $this->assertFloatEquals(
            $tfidf["the"],
            $expectedTfidf,
            0.01,
            "TF-IDF for 'the' (TF=2)"
        );

        // "cat" appears 1 time
        // TF-IDF = 1 * IDF["cat"] = IDF["cat"]
        $this->assertFloatEquals(
            $tfidf["cat"],
            $idf["cat"],
            0.01,
            "TF-IDF for 'cat' (TF=1) equals IDF"
        );

        echo "\n";
    }

    private function testTfidfWithOptions(): void
    {
        echo "Test 5: TF-IDF with Options\n";
        echo str_repeat('-', 50) . "\n";

        $docs = ["Café Coffee", "café Tea"];
        $idf = Text::idf($docs, ["lowercase" => true, "remove_diacritics" => true]);

        // Test that TF-IDF uses same preprocessing
        $tfidf = Text::tfidf("Café Coffee", $idf, [
            "lowercase" => true,
            "remove_diacritics" => true
        ]);

        $this->assertTrue(isset($tfidf["cafe"]), "TF-IDF includes 'cafe' with preprocessing");
        $this->assertTrue(isset($tfidf["coffee"]), "TF-IDF includes 'coffee'");

        echo "\n";
    }

    private function testTfidfPipeline(): void
    {
        echo "Test 6: Complete TF-IDF Pipeline\n";
        echo str_repeat('-', 50) . "\n";

        // Small corpus
        $corpus = [
            "Machine learning is a subset of artificial intelligence",
            "Deep learning is a subset of machine learning",
            "Neural networks are used in deep learning"
        ];

        // Calculate IDF
        $idf = Text::idf($corpus);

        // Calculate TF-IDF for each document
        $tfidfDocs = [];
        foreach ($corpus as $i => $doc) {
            $tfidfDocs[$i] = Text::tfidf($doc, $idf);
        }

        // "learning" appears in all 3 docs, should have lower TF-IDF
        // "intelligence" appears in 1 doc, should have higher TF-IDF
        $doc0 = $tfidfDocs[0];

        $this->assertTrue(isset($doc0["learning"]), "TF-IDF includes 'learning'");
        $this->assertTrue(isset($doc0["intelligence"]), "TF-IDF includes 'intelligence'");

        // Rare words should score higher than common words
        $this->assertFloatGreater(
            $doc0["intelligence"],
            $doc0["learning"],
            "Rare term 'intelligence' scores higher than common term 'learning'"
        );

        echo "\n";
    }

    private function testStemming(): void
    {
        echo "Test 7: Stemming in TF-IDF Pipeline\n";
        echo str_repeat('-', 50) . "\n";

        // Corpus with variations of "run"
        $corpus = [
            "running fast",
            "he runs quickly",
            "the runner is fast"
        ];

        // With stemming
        $idfStem = Text::idf($corpus, ["stem" => true]);

        // "run", "running", "runs" all stem to "run"
        $this->assertTrue(
            isset($idfStem["run"]),
            "IDF with stemming contains 'run' (from running/runs)"
        );

        // Without stemming
        $idfNoStem = Text::idf($corpus, ["stem" => false]);

        $this->assertTrue(
            isset($idfNoStem["running"]) && isset($idfNoStem["runs"]),
            "IDF without stemming keeps 'running' and 'runs' separate"
        );

        // TF-IDF with stemming should group variants
        $doc = "running runs runner";
        $tfidf = Text::tfidf($doc, $idfStem, ["stem" => true]);

        $this->assertTrue(
            isset($tfidf["run"]),
            "TF-IDF with stemming groups word variants"
        );

        // The stem "run" should have higher frequency (2 occurrences)
        $this->assertTrue(
            $tfidf["run"] > $tfidf["runner"],
            "Stemmed 'run' (TF=2) scores higher than 'runner' (TF=1)"
        );

        echo "\n";
    }

    private function testEdgeCases(): void
    {
        echo "Test 8: Edge Cases\n";
        echo str_repeat('-', 50) . "\n";

        // Empty corpus
        $idf = Text::idf([]);
        $this->assertEquals($idf, [], "Empty corpus returns empty IDF");

        // Single document
        $idf = Text::idf(["hello world"]);
        $this->assertTrue(isset($idf["hello"]), "Single document IDF works");

        // Term not in IDF dictionary
        $idf = Text::idf(["hello world"]);
        $tfidf = Text::tfidf("hello goodbye", $idf);
        $this->assertEquals($tfidf["goodbye"], 0.0, "Unknown term gets TF-IDF score of 0");

        // Empty document for TF-IDF
        $idf = Text::idf(["hello world"]);
        $tfidf = Text::tfidf("", $idf);
        $this->assertEquals($tfidf, [], "Empty document returns empty TF-IDF");

        echo "\n";
    }

    private function assertFloatEquals(float $actual, float $expected, float $tolerance, string $desc): void
    {
        $diff = abs($actual - $expected);
        if ($diff <= $tolerance) {
            echo "  ✓ {$desc}\n";
            if ($this->verbose) {
                echo "    Expected: {$expected}, Got: {$actual}\n";
            }
            $this->passed++;
        } else {
            echo "  ✗ {$desc}\n";
            echo "    Expected: {$expected}, Got: {$actual}, Diff: {$diff}\n";
            $this->failed++;
        }
    }

    private function assertFloatNotEquals(float $actual, float $notExpected, float $tolerance, string $desc): void
    {
        $diff = abs($actual - $notExpected);
        if ($diff > $tolerance) {
            echo "  ✓ {$desc}\n";
            $this->passed++;
        } else {
            echo "  ✗ {$desc}\n";
            echo "    Should not equal: {$notExpected}, Got: {$actual}\n";
            $this->failed++;
        }
    }

    private function assertFloatGreater(float $actual, float $threshold, string $desc): void
    {
        if ($actual > $threshold) {
            echo "  ✓ {$desc}\n";
            if ($this->verbose) {
                echo "    {$actual} > {$threshold}\n";
            }
            $this->passed++;
        } else {
            echo "  ✗ {$desc}\n";
            echo "    Expected {$actual} > {$threshold}\n";
            $this->failed++;
        }
    }

    private function assertTrue(bool $condition, string $desc): void
    {
        if ($condition) {
            echo "  ✓ {$desc}\n";
            $this->passed++;
        } else {
            echo "  ✗ {$desc}\n";
            $this->failed++;
        }
    }

    private function assertFalse(bool $condition, string $desc): void
    {
        if (!$condition) {
            echo "  ✓ {$desc}\n";
            $this->passed++;
        } else {
            echo "  ✗ {$desc}\n";
            $this->failed++;
        }
    }

    private function assertEquals($actual, $expected, string $desc): void
    {
        if ($actual === $expected) {
            echo "  ✓ {$desc}\n";
            $this->passed++;
        } else {
            echo "  ✗ {$desc}\n";
            echo "    Expected: " . json_encode($expected) . "\n";
            echo "    Got: " . json_encode($actual) . "\n";
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
$runner = new IdfTfidfTestRunner($verbose);
$runner->runTests();
