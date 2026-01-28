PHP_ARG_ENABLE(coralmedia, whether to enable coralmedia, [ --enable-coralmedia   Enable Coralmedia])

if test "$PHP_CORALMEDIA" = "yes"; then

	

	if ! test "x" = "x"; then
		PHP_EVAL_LIBLINE(, CORALMEDIA_SHARED_LIBADD)
	fi

	AC_DEFINE(HAVE_CORALMEDIA, 1, [Whether you have Coralmedia])
	coralmedia_sources="coralmedia.c kernel/main.c kernel/memory.c kernel/exception.c kernel/debug.c kernel/backtrace.c kernel/object.c kernel/array.c kernel/string.c kernel/fcall.c kernel/require.c kernel/file.c kernel/operators.c kernel/math.c kernel/concat.c kernel/variables.c kernel/filter.c kernel/iterator.c kernel/time.c kernel/exit.c coralmedia/stemmer/snowball.zep.c snowball_bridge.c
	libstemmer/libstemmer/libstemmer_utf8.c
	libstemmer/runtime/api.c
	libstemmer/runtime/utilities.c
	libstemmer/src_c/stem_UTF_8_danish.c
	libstemmer/src_c/stem_UTF_8_dutch.c
	libstemmer/src_c/stem_UTF_8_english.c
	libstemmer/src_c/stem_UTF_8_finnish.c
	libstemmer/src_c/stem_UTF_8_french.c
	libstemmer/src_c/stem_UTF_8_german.c
	libstemmer/src_c/stem_UTF_8_hungarian.c
	libstemmer/src_c/stem_UTF_8_italian.c
	libstemmer/src_c/stem_UTF_8_norwegian.c
	libstemmer/src_c/stem_UTF_8_porter.c
	libstemmer/src_c/stem_UTF_8_portuguese.c
	libstemmer/src_c/stem_UTF_8_romanian.c
	libstemmer/src_c/stem_UTF_8_russian.c
	libstemmer/src_c/stem_UTF_8_spanish.c
	libstemmer/src_c/stem_UTF_8_swedish.c
	libstemmer/src_c/stem_UTF_8_turkish.c"
	PHP_NEW_EXTENSION(coralmedia, $coralmedia_sources, $ext_shared,, )
	PHP_ADD_BUILD_DIR([$ext_builddir/kernel/])
	for dir in "coralmedia/stemmer"; do
		PHP_ADD_BUILD_DIR([$ext_builddir/$dir])
	done
	PHP_SUBST(CORALMEDIA_SHARED_LIBADD)

	old_CPPFLAGS=$CPPFLAGS
	CPPFLAGS="$CPPFLAGS $INCLUDES"

	AC_CHECK_DECL(
		[HAVE_BUNDLED_PCRE],
		[
			AC_CHECK_HEADERS(
				[ext/pcre/php_pcre.h],
				[
					PHP_ADD_EXTENSION_DEP([coralmedia], [pcre])
					AC_DEFINE([ZEPHIR_USE_PHP_PCRE], [1], [Whether PHP pcre extension is present at compile time])
				],
				,
				[[#include "main/php.h"]]
			)
		],
		,
		[[#include "php_config.h"]]
	)

	AC_CHECK_DECL(
		[HAVE_JSON],
		[
			AC_CHECK_HEADERS(
				[ext/json/php_json.h],
				[
					PHP_ADD_EXTENSION_DEP([coralmedia], [json])
					AC_DEFINE([ZEPHIR_USE_PHP_JSON], [1], [Whether PHP json extension is present at compile time])
				],
				,
				[[#include "main/php.h"]]
			)
		],
		,
		[[#include "php_config.h"]]
	)

	CPPFLAGS=$old_CPPFLAGS

	PHP_INSTALL_HEADERS([ext/coralmedia], [coralmedia/stemmer/snowball.zep.h])

fi
