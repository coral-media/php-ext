#!/usr/bin/env php
<?php

/**
 * Snowball Stemmer Examples
 * 
 * Demonstrates various use cases for the CoralMedia Stemmer
 */

echo "=== CoralMedia Snowball Stemmer Examples ===\n\n";

// Example 1: Basic English stemming
echo "Example 1: Basic English Stemming\n";
echo str_repeat('-', 50) . "\n";

$englishWords = [
    'running', 'runs', 'ran', 'runner',
    'easily', 'easier', 'easiest',
    'organizing', 'organization', 'organizational',
    'connected', 'connecting', 'connection', 'connections'
];

echo "Original words and their stems:\n";
foreach ($englishWords as $word) {
    $stem = CoralMedia\Stemmer\Snowball::stem($word);
    echo sprintf("  %-20s -> %s\n", $word, $stem);
}
echo "\n";

// Example 2: Spanish stemming
echo "Example 2: Spanish Stemming\n";
echo str_repeat('-', 50) . "\n";

$spanishWords = [
    'corriendo', 'correr', 'corrió', 'corredores',
    'comiendo', 'comer', 'comida', 'comidas',
    'trabajando', 'trabajador', 'trabajadores', 'trabajo'
];

echo "Palabras originales y sus raíces:\n";
foreach ($spanishWords as $word) {
    $stem = CoralMedia\Stemmer\Snowball::stem($word, 'spanish');
    echo sprintf("  %-20s -> %s\n", $word, $stem);
}
echo "\n";

// Example 3: Search/indexing use case
echo "Example 3: Text Search/Indexing\n";
echo str_repeat('-', 50) . "\n";

$documents = [
    "The runner is running quickly through the park",
    "Quick runners run faster than slow walkers",
    "Running shoes are designed for runners who run"
];

echo "Building search index with stemmed tokens:\n\n";

$index = [];
foreach ($documents as $docId => $doc) {
    $words = preg_split('/\s+/', strtolower($doc));
    $stems = [];
    
    foreach ($words as $word) {
        // Remove punctuation
        $clean = preg_replace('/[^a-z]/', '', $word);
        if ($clean) {
            $stem = CoralMedia\Stemmer\Snowball::stem($clean);
            $stems[] = $stem;
            
            // Build inverted index
            if (!isset($index[$stem])) {
                $index[$stem] = [];
            }
            if (!in_array($docId, $index[$stem])) {
                $index[$stem][] = $docId;
            }
        }
    }
    
    echo "Doc {$docId}: " . implode(', ', array_unique($stems)) . "\n";
}

echo "\nInverted index:\n";
foreach ($index as $stem => $docIds) {
    echo sprintf("  %-15s -> Documents: %s\n", $stem, implode(', ', $docIds));
}
echo "\n";

// Example 4: Query matching
echo "Example 4: Query Matching\n";
echo str_repeat('-', 50) . "\n";

$queries = ['running', 'runners', 'run'];

echo "Searching for: " . implode(', ', $queries) . "\n\n";

foreach ($queries as $query) {
    $queryStem = CoralMedia\Stemmer\Snowball::stem($query);
    $matches = $index[$queryStem] ?? [];
    
    echo sprintf("Query: '%s' (stem: '%s')\n", $query, $queryStem);
    if ($matches) {
        echo "  Found in documents: " . implode(', ', $matches) . "\n";
    } else {
        echo "  No matches found\n";
    }
}
echo "\n";

// Example 5: Deduplication
echo "Example 5: Text Deduplication\n";
echo str_repeat('-', 50) . "\n";

$variations = [
    'organization',
    'organizations', 
    'organizational',
    'organize',
    'organizing',
    'organized'
];

echo "Original variations:\n";
foreach ($variations as $word) {
    echo "  - {$word}\n";
}

$uniqueStems = array_unique(array_map(function($word) {
    return CoralMedia\Stemmer\Snowball::stem($word);
}, $variations));

echo "\nUnique stems: " . implode(', ', $uniqueStems) . "\n";
echo sprintf("Reduced from %d words to %d unique stem(s)\n\n", 
    count($variations), 
    count($uniqueStems)
);

// Example 6: Multi-language processing
echo "Example 6: Multi-Language Processing\n";
echo str_repeat('-', 50) . "\n";

$multiLang = [
    ['word' => 'connection', 'lang' => 'english'],
    ['word' => 'conexión', 'lang' => 'spanish'],
    ['word' => 'connected', 'lang' => 'english'],
    ['word' => 'conectado', 'lang' => 'spanish'],
];

echo "Processing multiple languages:\n";
foreach ($multiLang as $item) {
    $stem = CoralMedia\Stemmer\Snowball::stem($item['word'], $item['lang']);
    echo sprintf("  [%s] %-15s -> %s\n", 
        strtoupper(substr($item['lang'], 0, 2)), 
        $item['word'], 
        $stem
    );
}
echo "\n";

// Example 7: Performance comparison
echo "Example 7: Batch Processing Performance\n";
echo str_repeat('-', 50) . "\n";

$testText = str_repeat("running quickly through the connecting pathways ", 100);
$words = preg_split('/\s+/', strtolower(trim($testText)));

echo sprintf("Processing %d words...\n", count($words));

$start = microtime(true);
$stemmed = array_map(function($word) {
    return CoralMedia\Stemmer\Snowball::stem($word);
}, $words);
$duration = microtime(true) - $start;

echo sprintf("Completed in %.3f ms\n", $duration * 1000);
echo sprintf("Throughput: %s words/second\n", 
    number_format(count($words) / $duration, 0)
);
echo "\n";

echo "=== Examples Complete ===\n";