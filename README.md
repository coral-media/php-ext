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
php -r "echo CoralMedia\LinearAlgebra\Dot::calc([1,2,3], [4,5,6]), PHP_EOL; //32"
```

#### Vector norm

Computes the L<sub>1</sub>, L<sub>2</sub> or L<sub>∞</sub> norm of a numeric vector.

```bash
php -r "echo CoralMedia\LinearAlgebra::norm([1,2,3], 0), PHP_EOL;" // 6 && \
php -r "echo CoralMedia\LinearAlgebra::norm([1,2,3], 1), PHP_EOL;" // 3.741657 && \
php -r "echo CoralMedia\LinearAlgebra::norm([1,2,3], 2), PHP_EOL;" // 3
```

#### SVD - Singular Value Decomposition

```bash
php -r "print_r(CoralMedia\\LinearAlgebra::svd([1,2,3,4,5,6], 2, 3));" && \
php -r "print_r(CoralMedia\\LinearAlgebra::svdReduced([1,2,3,4,5,6], 2, 3));" && \
php -r "print_r(CoralMedia\\LinearAlgebra::svdFull([1,2,3,4,5,6], 2, 3));"
```

`svd()` expects matrices as a flat, row-major array.

```php
$x = [
  [1, 2, 3], 
  [4, 5, 6] 
];
$flat = array_merge(...$x);
$svd = CoralMedia\LinearAlgebra::svd($flat, 2, 3)
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
