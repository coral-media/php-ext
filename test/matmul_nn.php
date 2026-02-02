<?php

/**
 * Practical Example: Neural Network Forward Pass using Matrix Multiplication
 * 
 * This demonstrates how CoralMedia's matmul can be used for a simple
 * 2-layer neural network forward pass.
 */

echo "=== Neural Network Forward Pass Example ===\n\n";

// Input: 2 samples, 3 features each
// Shape: (2, 3)
$X = [
    1.0, 2.0, 3.0,    // Sample 1
    4.0, 5.0, 6.0     // Sample 2
];

// Layer 1 weights: 3 input features -> 4 hidden units
// Shape: (3, 4)
$W1 = [
    0.1, 0.2, 0.3, 0.4,   // Feature 1 weights
    0.5, 0.6, 0.7, 0.8,   // Feature 2 weights
    0.9, 1.0, 1.1, 1.2    // Feature 3 weights
];

// Layer 2 weights: 4 hidden units -> 2 output classes
// Shape: (4, 2)
$W2 = [
    0.1, 0.2,   // Hidden unit 1 weights
    0.3, 0.4,   // Hidden unit 2 weights
    0.5, 0.6,   // Hidden unit 3 weights
    0.7, 0.8    // Hidden unit 4 weights
];

echo "Input X (2 samples × 3 features):\n";
echo "[1.0  2.0  3.0]\n";
echo "[4.0  5.0  6.0]\n\n";

echo "Layer 1 Weights W1 (3 × 4):\n";
echo "[0.1  0.2  0.3  0.4]\n";
echo "[0.5  0.6  0.7  0.8]\n";
echo "[0.9  1.0  1.1  1.2]\n\n";

// Forward pass Layer 1: X × W1 = H
// (2, 3) × (3, 4) = (2, 4)
$H = CoralMedia\LinearAlgebra::matmul($X, $W1, 2, 3, 4);

echo "Hidden layer H = X × W1 (2 × 4):\n";
for ($i = 0; $i < 2; $i++) {
    echo "[";
    for ($j = 0; $j < 4; $j++) {
        echo sprintf("%.2f", $H[$i * 4 + $j]);
        echo ($j < 3) ? "  " : "";
    }
    echo "]\n";
}
echo "\n";

// Apply ReLU activation (simple max(0, x))
echo "After ReLU activation:\n";
$H_relu = array_map(function($x) { return max(0, $x); }, $H);
for ($i = 0; $i < 2; $i++) {
    echo "[";
    for ($j = 0; $j < 4; $j++) {
        echo sprintf("%.2f", $H_relu[$i * 4 + $j]);
        echo ($j < 3) ? "  " : "";
    }
    echo "]\n";
}
echo "\n";

echo "Layer 2 Weights W2 (4 × 2):\n";
echo "[0.1  0.2]\n";
echo "[0.3  0.4]\n";
echo "[0.5  0.6]\n";
echo "[0.7  0.8]\n\n";

// Forward pass Layer 2: H × W2 = Y
// (2, 4) × (4, 2) = (2, 2)
$Y = CoralMedia\LinearAlgebra::matmul($H_relu, $W2, 2, 4, 2);

echo "Output Y = H × W2 (2 × 2):\n";
for ($i = 0; $i < 2; $i++) {
    echo "[";
    for ($j = 0; $j < 2; $j++) {
        echo sprintf("%.2f", $Y[$i * 2 + $j]);
        echo ($j < 1) ? "  " : "";
    }
    echo "]\n";
}
echo "\n";

// Apply softmax to get probabilities
function softmax($row_start, $row_size, $Y) {
    $row = array_slice($Y, $row_start, $row_size);
    $max = max($row);
    $exp = array_map(function($x) use ($max) { return exp($x - $max); }, $row);
    $sum = array_sum($exp);
    return array_map(function($x) use ($sum) { return $x / $sum; }, $exp);
}

echo "After Softmax (probabilities):\n";
for ($i = 0; $i < 2; $i++) {
    $probs = softmax($i * 2, 2, $Y);
    echo sprintf("Sample %d: [%.4f  %.4f] -> Class %d (%.1f%% confidence)\n", 
        $i + 1, 
        $probs[0], 
        $probs[1], 
        $probs[0] > $probs[1] ? 0 : 1,
        max($probs) * 100
    );
}

echo "\n=== Example: Linear Regression ===\n\n";

// Design matrix X: 5 samples, 3 features (with bias)
// Shape: (5, 3)
$X_reg = [
    1, 2.5, 3.2,   // Sample 1 (bias=1, x1=2.5, x2=3.2)
    1, 1.8, 2.1,   // Sample 2
    1, 3.7, 4.5,   // Sample 3
    1, 2.1, 2.8,   // Sample 4
    1, 3.0, 3.5    // Sample 5
];

// Coefficients (weights)
// Shape: (3, 1)
$beta = [1.5, 2.0, -0.5];

echo "Design matrix X (5 × 3):\n";
echo "Bias  x1    x2\n";
for ($i = 0; $i < 5; $i++) {
    echo "[";
    for ($j = 0; $j < 3; $j++) {
        echo sprintf("%.1f", $X_reg[$i * 3 + $j]);
        echo ($j < 2) ? "  " : "";
    }
    echo "]\n";
}
echo "\n";

echo "Coefficients β (3 × 1): [1.5, 2.0, -0.5]\n\n";

// Predictions: y = X × β
// (5, 3) × (3, 1) = (5, 1)
$y_pred = CoralMedia\LinearAlgebra::matmul($X_reg, $beta, 5, 3, 1);

echo "Predictions (y = X × β):\n";
foreach ($y_pred as $i => $pred) {
    echo sprintf("Sample %d: %.2f\n", $i + 1, $pred);
}

echo "\n=== Example: Batch Processing ===\n\n";

// Process 100 samples with 784 features (like MNIST flattened images)
// through a weight matrix (784 → 128 hidden units)

$batch_size = 100;
$input_size = 784;
$hidden_size = 128;

echo "Simulating: {$batch_size} samples × {$input_size} features → {$hidden_size} hidden units\n";

// Generate random input (normally you'd load real data)
$X_batch = array_fill(0, $batch_size * $input_size, 0.1);

// Generate random weights
$W_batch = array_fill(0, $input_size * $hidden_size, 0.01);

// Time the operation
$start = microtime(true);
$H_batch = CoralMedia\LinearAlgebra::matmul($X_batch, $W_batch, $batch_size, $input_size, $hidden_size);
$duration = (microtime(true) - $start) * 1000;

echo sprintf("Matrix multiplication completed in %.2f ms\n", $duration);
echo sprintf("Result shape: %d × %d (%.1f MB)\n", $batch_size, $hidden_size, 
    ($batch_size * $hidden_size * 8) / (1024 * 1024));
echo sprintf("Total FLOPs: %s\n", 
    number_format(2 * $batch_size * $input_size * $hidden_size));

echo "\n=== All Examples Complete ===\n";