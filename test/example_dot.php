#!/usr/bin/env php
<?php

/**
 * Dot Product Examples
 * 
 * Demonstrates practical applications of the dot product operation
 */

echo "=== CoralMedia Dot Product Examples ===\n\n";

// Example 1: Basic calculations
echo "Example 1: Basic Dot Product Calculations\n";
echo str_repeat('-', 50) . "\n";

$vectors = [
    [[1, 2, 3], [4, 5, 6]],
    [[2, 3], [4, 5]],
    [[1, 0, 0], [0, 1, 0]],
];

foreach ($vectors as [$a, $b]) {
    $result = CoralMedia\LinearAlgebra::dot($a, $b);
    echo sprintf("  [%s] · [%s] = %.2f\n", 
        implode(', ', $a), 
        implode(', ', $b), 
        $result
    );
}
echo "\n";

// Example 2: Computing vector magnitude (norm)
echo "Example 2: Vector Magnitude via Dot Product\n";
echo str_repeat('-', 50) . "\n";
echo "Formula: |v| = √(v · v)\n\n";

$vectors = [
    [3, 4],           // |v| = 5
    [1, 2, 2],        // |v| = 3
    [1, 1, 1, 1],     // |v| = 2
];

foreach ($vectors as $v) {
    $dotProduct = CoralMedia\LinearAlgebra::dot($v, $v);
    $magnitude = sqrt($dotProduct);
    echo sprintf("  v = [%s]\n", implode(', ', $v));
    echo sprintf("    v · v = %.2f\n", $dotProduct);
    echo sprintf("    |v| = √%.2f = %.4f\n\n", $dotProduct, $magnitude);
}

// Example 3: Cosine Similarity
echo "Example 3: Cosine Similarity for Document Comparison\n";
echo str_repeat('-', 50) . "\n";

function cosineSimilarity($a, $b) {
    $dotProduct = CoralMedia\LinearAlgebra::dot($a, $b);
    $magnitudeA = sqrt(CoralMedia\LinearAlgebra::dot($a, $a));
    $magnitudeB = sqrt(CoralMedia\LinearAlgebra::dot($b, $b));
    return $dotProduct / ($magnitudeA * $magnitudeB);
}

// Word frequency vectors for three documents
// Vocabulary: [cat, dog, fish, bird, house]
$doc1 = [3, 2, 0, 1, 0];  // "cat cat cat dog dog bird"
$doc2 = [2, 1, 1, 0, 0];  // "cat cat dog fish"
$doc3 = [0, 0, 2, 1, 3];  // "fish fish bird house house house"

echo "Documents represented as word frequency vectors:\n";
echo "  Doc1: [3, 2, 0, 1, 0] - about cats and dogs\n";
echo "  Doc2: [2, 1, 1, 0, 0] - about cats, dogs, fish\n";
echo "  Doc3: [0, 0, 2, 1, 3] - about fish, birds, houses\n\n";

$sim_1_2 = cosineSimilarity($doc1, $doc2);
$sim_1_3 = cosineSimilarity($doc1, $doc3);
$sim_2_3 = cosineSimilarity($doc2, $doc3);

echo "Cosine similarities (0 = unrelated, 1 = identical):\n";
echo sprintf("  Doc1 ↔ Doc2: %.4f (both about pets)\n", $sim_1_2);
echo sprintf("  Doc1 ↔ Doc3: %.4f (different topics)\n", $sim_1_3);
echo sprintf("  Doc2 ↔ Doc3: %.4f (share 'fish')\n", $sim_2_3);
echo "\n";

// Example 4: Projection of one vector onto another
echo "Example 4: Vector Projection\n";
echo str_repeat('-', 50) . "\n";
echo "Project vector v onto vector u: proj_u(v) = (v·u / u·u) * u\n\n";

$v = [3, 4];
$u = [1, 0];

$dotVU = CoralMedia\LinearAlgebra::dot($v, $u);
$dotUU = CoralMedia\LinearAlgebra::dot($u, $u);
$scalar = $dotVU / $dotUU;

echo sprintf("  v = [%s]\n", implode(', ', $v));
echo sprintf("  u = [%s]\n", implode(', ', $u));
echo sprintf("  v · u = %.2f\n", $dotVU);
echo sprintf("  u · u = %.2f\n", $dotUU);
echo sprintf("  Projection scalar: %.2f\n", $scalar);
echo sprintf("  proj_u(v) = [%.2f, %.2f]\n\n", $scalar * $u[0], $scalar * $u[1]);

// Example 5: Checking orthogonality
echo "Example 5: Testing Vector Orthogonality\n";
echo str_repeat('-', 50) . "\n";
echo "Two vectors are orthogonal (perpendicular) if their dot product = 0\n\n";

$vectorPairs = [
    [[1, 0], [0, 1], "Unit x and y"],
    [[3, 4], [-4, 3], "Perpendicular 2D"],
    [[1, 2, 3], [1, 2, 3], "Same vector"],
    [[2, -1], [1, 2], "Perpendicular"],
];

foreach ($vectorPairs as [$a, $b, $desc]) {
    $dot = CoralMedia\LinearAlgebra::dot($a, $b);
    $isOrthogonal = abs($dot) < 0.0001;
    
    echo sprintf("  %s\n", $desc);
    echo sprintf("    [%s] · [%s] = %.2f\n", 
        implode(', ', $a), 
        implode(', ', $b), 
        $dot
    );
    echo sprintf("    %s\n\n", 
        $isOrthogonal ? "✓ Orthogonal" : "✗ Not orthogonal"
    );
}

// Example 6: Work calculation in physics
echo "Example 6: Work Calculation (Physics)\n";
echo str_repeat('-', 50) . "\n";
echo "Work = Force · Displacement\n\n";

// Force vector (Newtons)
$force = [10, 5, 0];  // 10N in x, 5N in y, 0 in z

// Displacement vector (meters)
$displacement = [3, 2, 1];  // 3m in x, 2m in y, 1m in z

$work = CoralMedia\LinearAlgebra::dot($force, $displacement);

echo sprintf("  Force:        F = [%s] N\n", implode(', ', $force));
echo sprintf("  Displacement: d = [%s] m\n", implode(', ', $displacement));
echo sprintf("  Work:         W = F · d = %.2f Joules\n\n", $work);

// Example 7: Weighted sum
echo "Example 7: Weighted Sum / Linear Combination\n";
echo str_repeat('-', 50) . "\n";

// Feature values for a product
$features = [4.5, 8.2, 9.1, 7.3];  // ratings for different aspects

// Weights (importance of each feature)
$weights = [0.3, 0.4, 0.2, 0.1];   // sum to 1.0

$weightedScore = CoralMedia\LinearAlgebra::dot($features, $weights);

echo "Product rating calculation:\n";
echo "  Feature scores: [" . implode(', ', $features) . "]\n";
echo "  Weights:        [" . implode(', ', $weights) . "]\n";
echo sprintf("  Weighted score: %.2f / 10\n\n", $weightedScore);

// Example 8: Distance calculation (squared Euclidean)
echo "Example 8: Squared Euclidean Distance\n";
echo str_repeat('-', 50) . "\n";
echo "Distance² = |a - b|² = (a-b) · (a-b)\n\n";

$pointA = [1, 2, 3];
$pointB = [4, 6, 8];

// Compute difference vector
$diff = [];
for ($i = 0; $i < count($pointA); $i++) {
    $diff[] = $pointA[$i] - $pointB[$i];
}

$distanceSquared = CoralMedia\LinearAlgebra::dot($diff, $diff);
$distance = sqrt($distanceSquared);

echo sprintf("  Point A: [%s]\n", implode(', ', $pointA));
echo sprintf("  Point B: [%s]\n", implode(', ', $pointB));
echo sprintf("  Difference: [%s]\n", implode(', ', $diff));
echo sprintf("  Distance²: %.2f\n", $distanceSquared);
echo sprintf("  Distance: %.4f\n\n", $distance);

// Example 9: Correlation coefficient computation
echo "Example 9: Computing Correlation\n";
echo str_repeat('-', 50) . "\n";

$x = [1, 2, 3, 4, 5];
$y = [2, 4, 5, 4, 5];

// Center the data (subtract mean)
$meanX = array_sum($x) / count($x);
$meanY = array_sum($y) / count($y);

$xCentered = array_map(fn($v) => $v - $meanX, $x);
$yCentered = array_map(fn($v) => $v - $meanY, $y);

// Correlation = (x_centered · y_centered) / (|x_centered| * |y_centered|)
$dotXY = CoralMedia\LinearAlgebra::dot($xCentered, $yCentered);
$normX = sqrt(CoralMedia\LinearAlgebra::dot($xCentered, $xCentered));
$normY = sqrt(CoralMedia\LinearAlgebra::dot($yCentered, $yCentered));

$correlation = $dotXY / ($normX * $normY);

echo "  X: [" . implode(', ', $x) . "]\n";
echo "  Y: [" . implode(', ', $y) . "]\n";
echo sprintf("  Correlation coefficient: %.4f\n", $correlation);
echo sprintf("    (%.4f = %s correlation)\n\n", 
    $correlation,
    $correlation > 0.7 ? "strong positive" : 
        ($correlation > 0.3 ? "moderate positive" : "weak")
);

// Example 10: Neural network layer (simplified)
echo "Example 10: Neural Network - Single Neuron\n";
echo str_repeat('-', 50) . "\n";

// Input features
$input = [0.5, 0.8, 0.3];

// Neuron weights
$weights = [0.4, 0.6, -0.2];

// Bias
$bias = 0.1;

// Weighted sum (before activation)
$weightedSum = CoralMedia\LinearAlgebra::dot($input, $weights) + $bias;

// Apply ReLU activation
$output = max(0, $weightedSum);

echo "  Input:    [" . implode(', ', $input) . "]\n";
echo "  Weights:  [" . implode(', ', $weights) . "]\n";
echo "  Bias:     {$bias}\n";
echo sprintf("  Weighted sum: %.4f\n", $weightedSum);
echo sprintf("  Output (ReLU): %.4f\n\n", $output);

echo "=== Examples Complete ===\n";