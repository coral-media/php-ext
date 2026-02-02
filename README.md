# CoralMedia PHP Extension (Zephir)

CoralMedia is a **Zephir-based PHP extension** that exposes high-performance native libraries through clean, stable PHP APIs.
Its purpose is to provide a **single native runtime** for performance‑critical workloads while preserving PHP ergonomics.

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

---

## Requirements

- PHP 8.x
- Zephir 0.19
- C toolchain (gcc/clang)
- zephir_parser
- OpenBLAS development libraries

Zephir requires the zephir_parser PHP extension to be installed and enabled at build time.
Repository: [https://github.com/zephir-lang/php-zephir-parser](https://github.com/zephir-lang/php-zephir-parser)
Documentation: [https://docs.zephir-lang.com/](https://docs.zephir-lang.com/latest/introduction/)

Install Zephir tooling:

```bash
composer global require phalcon/zephir
```

This extension uses OpenBLAS for high-performance linear algebra operations.

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

---

## Build & Install

```bash
zephir fullclean
zephir build
printf "extension=coralmedia.so\n" > /usr/local/etc/php/conf.d/coralmedia.ini
```

---

## License

MIT