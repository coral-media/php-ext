# CoralMedia PHP Extension (Zephir)

CoralMedia is a **Zephir-based PHP extension** that exposes high-performance native libraries through clean, stable PHP APIs.
Its purpose is to provide a **single native runtime** for performance‑critical workloads while preserving PHP ergonomics.

---

## Current Features

### Snowball Stemmer (libstemmer)

Fast UTF‑8 stemming for NLP / IR pipelines backed by [zvelo's Snowball implementation](https://github.com/zvelo/libstemmer).

```php
echo CoralMedia\Stemmer\Snowball::stem("running", "english");
// run
```

---

### Linear Algebra

#### Dot Product

Native vector dot product backed by [CControl](https://github.com/DanielMartensson/CControl) primitives.

```php
$result = CoralMedia\LinearAlgebra\Dot::calc([1,2,3], [4,5,6]);
// 32
```

---

## Requirements

- PHP 8.x
- Zephir 0.19
- C toolchain (gcc/clang)
- zephir_parser

Zephir requires the zephir_parser PHP extension to be installed and enabled at build time.
Repository: [https://github.com/zephir-lang/php-zephir-parser](https://github.com/zephir-lang/php-zephir-parser)
Documentation: [https://docs.zephir-lang.com/](https://docs.zephir-lang.com/latest/introduction/)

Install Zephir tooling:
```bash
composer global require phalcon/zephir
```

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
