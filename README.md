# CoralMedia PHP Extension (Zephir)

CoralMedia is a **Zephir-based PHP extension** that exposes high-performance native libraries through clean, stable PHP APIs.
Its purpose is to provide a **single native runtime** for performance‑critical workloads while preserving PHP ergonomics.

---

## Requirements

- PHP 8.x
- Zephir 0.19
- C toolchain (gcc/clang)
- zephir_parser
- OpenBLAS development libraries
- ICU (International Components for Unicode) development libraries

Zephir requires the zephir_parser PHP extension to be installed and enabled at build time.
Repository: [https://github.com/zephir-lang/php-zephir-parser](https://github.com/zephir-lang/php-zephir-parser)
Documentation: [https://docs.zephir-lang.com/](https://docs.zephir-lang.com/latest/introduction/)

Install Zephir tooling:

```bash
composer global require phalcon/zephir
```

This extension uses OpenBLAS for high-performance linear algebra operations and ICU for Unicode text processing.

### OpenBLAS installation

**Debian / Ubuntu**

```bash
apt install libopenblas-dev
```

**macOS (Homebrew)**

```bash
brew install openblas
```

**Windows**

- Install OpenBLAS via MSYS2 (mingw-w64-x86_64-openblas)
- Ensure openblas.dll is available in PATH at runtime

### ICU installation

**Debian / Ubuntu**

```bash
apt install libicu-dev
```

**macOS (Homebrew)**

```bash
brew install icu4c
```

**Windows**

- Install ICU via MSYS2 (mingw-w64-x86_64-icu)
- Ensure ICU DLLs are available in PATH at runtime

---

## Build & Install

```bash
zephir fullclean
zephir build
printf "extension=coralmedia.so\n" > /usr/local/etc/php/conf.d/coralmedia.ini
```

---

## Current Features

### Snowball Stemmer (libstemmer)

Fast UTF‑8 stemming for NLP / IR pipelines backed by [zvelo's Snowball implementation](https://github.com/zvelo/libstemmer).

```bash
php -r "echo CoralMedia\\Stemmer\\Snowball::stem('attackers'), PHP_EOL;" && \
php -r "echo CoralMedia\\Stemmer\\Snowball::stem('attacking'), PHP_EOL;" && \
php -r "echo CoralMedia\\Stemmer\\Snowball::stem('attacked'), PHP_EOL;" && \

php -r "echo CoralMedia\\Stemmer\\Snowball::stem('haciéndole', 'spanish'), PHP_EOL;" && \
php -r "echo CoralMedia\\Stemmer\\Snowball::stem('haciendole', 'spanish'), PHP_EOL;" && \
php -r "echo CoralMedia\\Stemmer\\Snowball::stem('haciéndonos', 'spanish'), PHP_EOL;" && \
php -r "echo CoralMedia\\Stemmer\\Snowball::stem('haciendonos', 'spanish'), PHP_EOL;"
```

---

### Linear Algebra

Backed by [OpenBLAS](https://github.com/OpenMathLib/OpenBLAS) primitives

#### Dot Product

Computes the dot product of two numeric vectors.

```bash
php -r "echo CoralMedia\LinearAlgebra::dot([1,2,3], [4,5,6]), PHP_EOL; //32"
```

#### Vector norm

Computes the L<sub>1</sub>, L<sub>2</sub> or L<sub>∞</sub> norm of a numeric vector.

```bash
php -r "echo CoralMedia\LinearAlgebra::norm([1,2,3], CoralMedia\Constants::LA_NORM_L1), PHP_EOL;" // 6 && \
php -r "echo CoralMedia\LinearAlgebra::norm([1,2,3], CoralMedia\Constants::LA_NORM_L2), PHP_EOL;" // 3.741657 && \
php -r "echo CoralMedia\LinearAlgebra::norm([1,2,3], CoralMedia\Constants::LA_NORM_LINF), PHP_EOL;" // 3
```

#### Normalize vector

Returns normalized vector.

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::normalize([1, -2, 3]));" && \
php -r "print_r(CoralMedia\\LinearAlgebra::normalize([1, -2, 3], CoralMedia\Constants::LA_NORM_L2));" && \
php -r "print_r(CoralMedia\\LinearAlgebra::normalize([1, 2, 3], CoralMedia\Constants::LA_NORM_L1));" && \
php -r "print_r(CoralMedia\\LinearAlgebra::normalize([1, -5, 3], CoralMedia\Constants::LA_NORM_LINF));"
```

#### Vector distance

Computes the distance between two numeric vectors of equal length.

- L1 (Manhattan)
- L2 (Euclidean)
- Lp (Minkowski) with configurable p

```bash
php -r "echo CoralMedia\\LinearAlgebra::distance([1,2,3], [4,5,6]), PHP_EOL;" && \
php -r "echo CoralMedia\\LinearAlgebra::distance([1,2,3], [4,5,6], CoralMedia\\Constants::LA_DIST_L1), PHP_EOL;" && \
php -r "echo CoralMedia\\LinearAlgebra::distance([1,2,3], [4,5,6], CoralMedia\\Constants::LA_DIST_LP, 3), PHP_EOL;"
```

#### Cosine distance

Measures angular distance (dissimilarity) between two vectors.
If vectors have lenght `|1|` use `1 - dot(x, y)` instead or `dot(x, y)` for cosine similarity.

```bash
php -r "echo CoralMedia\\LinearAlgebra::distance([1, 2, 3],[4, 5, 6], CoralMedia\\Constants::LA_DIST_COS), PHP_EOL;" && \
php -r "echo CoralMedia\\LinearAlgebra::distance(CoralMedia\\LinearAlgebra::normalize([1,2,3]), CoralMedia\\LinearAlgebra::normalize([4,5,6]), CoralMedia\\Constants::LA_DIST_COS), PHP_EOL;" && \
php -r "echo 1 - CoralMedia\\LinearAlgebra::dot(CoralMedia\\LinearAlgebra::normalize([1,2,3]), CoralMedia\\LinearAlgebra::normalize([4,5,6])), PHP_EOL;"
php -r "echo CoralMedia\\LinearAlgebra::dot(CoralMedia\\LinearAlgebra::normalize([1,2,3]), CoralMedia\\LinearAlgebra::normalize([4,5,6])), PHP_EOL;"
```

#### SVD - Singular Value Decomposition

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::svd([1,2,3,4,5,6], 2, 3, CoralMedia\Constants::LA_SVD_VALUES));" && \
php -r "print_r(CoralMedia\\LinearAlgebra::svd([1,2,3,4,5,6], 2, 3, CoralMedia\Constants::LA_SVD_REDUCED));" && \
php -r "print_r(CoralMedia\\LinearAlgebra::svd([1,2,3,4,5,6], 2, 3, CoralMedia\Constants::LA_SVD_FULL));"
```

`svd()` expects matrices as a flat, row-major array.

```php
$x = [
  [1, 2, 3], 
  [4, 5, 6] 
];
$flat = array_merge(...$x);
$svd = CoralMedia\LinearAlgebra::svd($flat, count($x), count($x[0]));
```

#### Matrix Multiplication (GEMM)

High-performance matrix multiplication using OpenBLAS's `cblas_sgemm`.

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::matmul([1,2,3,4,5,6], [7,8,9,10,11,12], 2, 3, 2));" && \
php -r "print_r(CoralMedia\\LinearAlgebra::matmul([1,2,3,4], [5,6,7,8], 2, 2, 2));" && \
php -r "print_r(CoralMedia\\LinearAlgebra::matmul([1,0,0,0,2,0,0,0,3], [2,3,4], 3, 3, 1));" && \
php -r "print_r(CoralMedia\\LinearAlgebra::matmul([1,2,3,4], [5,7,6,8], 2, 2, 2, false, true));"
```

**Function signature:**
```php
CoralMedia\LinearAlgebra::matmul(
    array $a,              // Matrix A (flat, row-major)
    array $b,              // Matrix B (flat, row-major)
    int $m,                // Rows in A (or A^T if transposed)
    int $n,                // Cols in A / Rows in B (shared dimension)
    int $k,                // Cols in B (or B^T if transposed)
    bool $transpose_a,     // Transpose A? (default: false)
    bool $transpose_b      // Transpose B? (default: false)
): array                   // Result matrix C (m×k, flat row-major)
```

Matrices must be provided as flat arrays in **row-major order**:
```php
// PHP 2D array
$A = [
  [1, 2, 3],
  [4, 5, 6]
];

// Convert to flat row-major
$flat = array_merge(...$A);  // [1,2,3,4,5,6]

// Multiply with B (3×2)
$result = CoralMedia\LinearAlgebra::matmul($flat, $B, 2, 3, 2);

// Convert result back to 2D (2×2)
$C = array_chunk($result, 2);
```

#### Element-wise Matrix Operations

Element-wise operations that apply operations to corresponding elements of matrices or apply scalar operations to all elements.

##### Binary Element-wise Operations

**Matrix Addition** - Element-wise addition: `C[i] = A[i] + B[i]`

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::matrixAdd([1,2,3,4], [5,6,7,8], 2, 2));"
# Output: [6, 8, 10, 12]
```

**Matrix Subtraction** - Element-wise subtraction: `C[i] = A[i] - B[i]`

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::matrixSubtract([10,20,30,40], [1,2,3,4], 2, 2));"
# Output: [9, 18, 27, 36]
```

**Hadamard Product** - Element-wise multiplication: `C[i] = A[i] × B[i]`

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::matrixHadamard([2,3,4,5], [1,2,3,4], 2, 2));"
# Output: [2, 6, 12, 20]
```

**Element-wise Division** - Element-wise division: `C[i] = A[i] / B[i]`

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::matrixDivide([10,20,30,40], [2,4,5,8], 2, 2));"
# Output: [5, 5, 6, 5]
```

##### Scalar Operations

**Matrix Scale** - Multiply all elements by scalar: `C[i] = scalar × A[i]`

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::matrixScale([1,2,3,4], 2.5, 2, 2));"
# Output: [2.5, 5, 7.5, 10]
```

**Add Scalar** - Add scalar to all elements: `C[i] = A[i] + scalar`

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::matrixAddScalar([1,2,3,4], 10.0, 2, 2));"
# Output: [11, 12, 13, 14]
```

**Multiply Scalar** - Multiply all elements by scalar: `C[i] = scalar × A[i]`

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::matrixMultiplyScalar([2,4,6,8], 0.5, 2, 2));"
# Output: [1, 2, 3, 4]
```

**Divide by Scalar** - Divide all elements by scalar: `C[i] = A[i] / scalar`

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::matrixDivideScalar([10,20,30,40], 10.0, 2, 2));"
# Output: [1, 2, 3, 4]
```

**Function signatures:**
```php
// Binary operations - require two matrices of same dimensions
CoralMedia\LinearAlgebra::matrixAdd(array $a, array $b, int $rows, int $cols): array
CoralMedia\LinearAlgebra::matrixSubtract(array $a, array $b, int $rows, int $cols): array
CoralMedia\LinearAlgebra::matrixHadamard(array $a, array $b, int $rows, int $cols): array
CoralMedia\LinearAlgebra::matrixDivide(array $a, array $b, int $rows, int $cols): array

// Scalar operations - apply scalar to all elements
CoralMedia\LinearAlgebra::matrixScale(array $a, float $scalar, int $rows, int $cols): array
CoralMedia\LinearAlgebra::matrixAddScalar(array $a, float $scalar, int $rows, int $cols): array
CoralMedia\LinearAlgebra::matrixMultiplyScalar(array $a, float $scalar, int $rows, int $cols): array
CoralMedia\LinearAlgebra::matrixDivideScalar(array $a, float $scalar, int $rows, int $cols): array
```

**Error handling:**
- Binary operations throw `ValueError` if matrix dimensions don't match
- `matrixDivide()` throws `ValueError` if any element in matrix B is zero
- `matrixDivideScalar()` throws `ValueError` if scalar is zero
- All operations throw `TypeError` if arguments are not arrays (for matrices) or numeric (for scalars)

**Example: Chaining operations**
```php
use CoralMedia\LinearAlgebra;

// Scale matrix, add scalar, then element-wise multiply
$a = [1, 2, 3, 4];
$scaled = LinearAlgebra::matrixScale($a, 2.0, 2, 2);        // [2, 4, 6, 8]
$added = LinearAlgebra::matrixAddScalar($scaled, 1.0, 2, 2); // [3, 5, 7, 9]
$result = LinearAlgebra::matrixHadamard($added, [1,1,1,1], 2, 2); // [3, 5, 7, 9]
```

---

### Text Processing

#### ICU Tokenizer

High-performance Unicode text tokenization using [ICU (International Components for Unicode)](https://icu.unicode.org/) for proper word and sentence boundary detection.

##### Word Breaking

Tokenize text into words with proper Unicode segmentation. Handles languages without spaces (Japanese, Thai, Chinese) and complex boundary rules.

```bash
# English
php -r "print_r(CoralMedia\\Text::wordBreak('Hello world'));"
# Output: Array([0]=>Hello [1]=>world)

# Japanese (no spaces)
php -r "print_r(CoralMedia\\Text::wordBreak('私は学生です', 'ja_JP'));"
# Output: Array([0]=>私 [1]=>は [2]=>学生 [3]=>です)

# Thai (no spaces, dictionary-based)
php -r "print_r(CoralMedia\\Text::wordBreak('สวัสดีครับ', 'th_TH'));"
# Output: Array([0]=>สวัสดี [1]=>ครับ)

# Chinese (character-based)
php -r "print_r(CoralMedia\\Text::wordBreak('我爱中国', 'zh_CN'));"
# Output: Array([0]=>我 [1]=>爱 [2]=>中国)
```

##### Sentence Breaking

Split text into sentences using ICU sentence boundary analysis. Handles abbreviations and language-specific rules.

```bash
# English sentences
php -r "print_r(CoralMedia\\Text::sentenceBreak('Hello. World.'));"
# Output: Array([0]=>Hello.  [1]=>World.)

# Japanese sentences
php -r "print_r(CoralMedia\\Text::sentenceBreak('こんにちは。元気ですか。', 'ja_JP'));"
# Output: Array([0]=>こんにちは。 [1]=>元気ですか。)
```

##### Case Normalization

Convert text to lowercase using ICU locale-aware case mapping. Handles locale-specific rules like Turkish dotted/dotless I.

```bash
php -r "echo CoralMedia\\Text::lowercase('HELLO WORLD');" && \
php -r "echo CoralMedia\\Text::lowercase('CAFÉ');" && \
php -r "echo CoralMedia\\Text::lowercase('İSTANBUL', 'tr_TR');" && \
php -r "echo CoralMedia\\Text::lowercase('ΑΘΗΝΑ', 'el_GR');" && \
php -r "echo CoralMedia\\Text::lowercase('МОСКВА', 'ru_RU');"
```

**Function signatures:**
```php
CoralMedia\Text::wordBreak(string $text, string $locale = "en_US"): array
CoralMedia\Text::sentenceBreak(string $text, string $locale = "en_US"): array
CoralMedia\Text::lowercase(string $text, string $locale = "en_US"): string
```

**Supported locales:**
- `en_US` - English (United States)
- `ja_JP` - Japanese
- `zh_CN` - Chinese (Simplified)
- `th_TH` - Thai
- And many more ICU-supported locales

**Key features:**
- Proper Unicode word segmentation for languages without spaces
- Dictionary-based breaking for Thai, Myanmar, Khmer, Lao
- Morphological analysis for Japanese, Chinese, Korean
- Locale-specific rules for contractions, abbreviations, numbers
- Locale-aware case normalization (handles Turkish İ/I, Greek Σ/ς, etc.)
- Significantly more accurate than regex-based tokenization

---

## License

MIT