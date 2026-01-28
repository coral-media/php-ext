# CoralMedia PHP Extension (Zephir)

A modular Zephir-based PHP extension that provides a **single, cohesive native runtime** for multiple C/C++ 
libraries (starting with Snowball/libstemmer), exposing a clean PHP API while keeping performance-critical work in native code.

## Goals

- Provide a **stable extension foundation** where multiple native libraries can be added over time.
- Keep PHP-facing APIs **simple, predictable, and versioned**.
- Use Zephir **optimizers** to translate specific function calls into efficient C calls (no userland overhead).
- Keep each native library in its **own section** (code + docs + tests).

## Current Features

### Snowball Stemmer (libstemmer)

- Provides stemming using Snowball algorithms via `libstemmer`.
- Designed for **high-throughput token normalization** workflows (IR / NLP pipelines).

**PHP API**
```php
<?php

echo CoralMedia\Stemmer\Snowball::stem("running", "english"), PHP_EOL;
// expected: run
```

**Supported languages**
Depends on which `stem_UTF_8_*.c` sources you compile into the extension (see `config.json` → `extra-sources`).
In the current setup, English and Spanish are compiled in.

## Requirements

### Runtime
- PHP **8.3**
- Debian-based environment (recommended for build tooling stability)

### Build-time
- Zephir (CLI)
- `zephir_parser` extension
- C toolchain (gcc/clang, make, autoconf, libtool, pkg-config)

### Composer (Zephir tooling)
```bash
composer require phalcon/zephir
```

### Zephir Parser
You must have **zephir_parser** installed and enabled for your PHP build.

- Repository: https://github.com/zephir-lang/php-zephir-parser
- Zephir docs: https://docs.zephir-lang.com/

## Project Structure (Conceptual)

- `coralmedia/` (Zephir source)
- `optimizers/` (Zephir optimizer classes)
- `ext/` (generated extension + custom C bridge files)
- `ext/snowball_bridge.c` / `ext/snowball_bridge.h` (native bridge API)
- `libstemmer/` (vendored libstemmer source tree, or staged sources)


## How It Works

### 1) The Zephir class calls a “virtual” function

Your Zephir API is intentionally clean:

```zep
namespace CoralMedia\Stemmer;

class Snowball
{
    public static function stem(string word, string lang = "english") -> string | null
    {
        // Intercepted by the optimizer and replaced with a native call.
        return libstemmer_stem(word, lang);
    }
}
```

### 2) The optimizer intercepts and emits C code

A Zephir optimizer replaces `libstemmer_stem(word, lang)` with a direct C call that returns a `zend_string*`, wrapped back into a zval via `ZVAL_STR(...)`.

Optimizer docs:
- https://docs.zephir-lang.com/latest/optimizers/

### 3) The C bridge calls libstemmer

The bridge implements something like:

- `zend_string *libstemmer_stem(zend_string *word, const char *lang);`

and internally uses:

- `sb_stemmer_new()`
- `sb_stemmer_stem()`
- `sb_stemmer_length()`
- `sb_stemmer_delete()`

## Building

A typical flow (inside the project root):

```bash
zephir fullclean
zephir build
```

Enable the extension (example):

```bash
printf "extension=coralmedia.so\n" > /usr/local/etc/php/conf.d/coralmedia.ini
```

Verify:

```bash
php -r 'var_dump(extension_loaded("coralmedia"));'
```

## `config.json` Notes

This project uses `config.json` to drive build behavior, including compilation of extra C sources and optimizer discovery.

### Key fields used here

- `extra-sources`
  - Adds custom C files (bridge) and vendored library sources (libstemmer) to the build.
- `extra-cflags`
  - Include paths for vendored headers and (optionally) warning suppressions.
- `optimizer-dirs`
  - Directories Zephir scans to load optimizer classes.

Example snippet:
```json
{
  "optimizer-dirs": ["optimizers"],
  "extra-cflags": "-I./libstemmer/include -I./libstemmer",
  "extra-sources": [
    "snowball_bridge.c",
    "libstemmer/libstemmer/libstemmer_utf8.c",
    "libstemmer/runtime/api.c",
    "libstemmer/runtime/utilities.c",
    "libstemmer/src_c/stem_UTF_8_english.c",
    "libstemmer/src_c/stem_UTF_8_spanish.c"
  ]
}
```

## Adding New Native Libraries

The intended pattern for each new library:

1. Add a dedicated **C bridge** (e.g., `ext/<lib>_bridge.c/.h`)
2. Add relevant files to `extra-sources` and include paths to `extra-cflags`
3. Expose a clean Zephir API class (e.g., `CoralMedia\<Domain>\<Feature>`)
4. Add an optimizer to intercept a function call and emit the native call
5. Add tests + docs section

## Roadmap / Next Steps

Candidate libraries that could deliver strong ROI in IR/ML and scientific workloads:

- **LAPACK (Embedded / bundled)**  
  For linear algebra primitives (SVD, eigen, least-squares) powering clustering, PCA, etc.
- **BLAS implementations** (OpenBLAS / BLIS)  
  For fast matrix/vector ops.
- **ANN / similarity search** libraries  
  For scalable retrieval workloads.

Each will get its own section in this README as it lands.

## License

MIT (see `LICENSE`).
