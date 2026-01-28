
#ifdef HAVE_CONFIG_H
#include "../../ext_config.h"
#endif

#include <php.h>
#include "../../php_ext.h"
#include "../../ext.h"

#include <Zend/zend_operators.h>
#include <Zend/zend_exceptions.h>
#include <Zend/zend_interfaces.h>

#include "kernel/main.h"
#include "snowball_bridge.h"
#include "kernel/memory.h"
#include "kernel/operators.h"
#include "kernel/object.h"


ZEPHIR_INIT_CLASS(CoralMedia_Stemmer_Snowball)
{
	ZEPHIR_REGISTER_CLASS(CoralMedia\\Stemmer, Snowball, coralmedia, stemmer_snowball, coralmedia_stemmer_snowball_method_entry, 0);

	return SUCCESS;
}

PHP_METHOD(CoralMedia_Stemmer_Snowball, stem)
{
	zephir_method_globals *ZEPHIR_METHOD_GLOBALS_PTR = NULL;
	zval *word_param = NULL, *lang_param = NULL;
	zval word, lang, _0;

	ZVAL_UNDEF(&word);
	ZVAL_UNDEF(&lang);
	ZVAL_UNDEF(&_0);
	ZEND_PARSE_PARAMETERS_START(1, 2)
		Z_PARAM_STR(word)
		Z_PARAM_OPTIONAL
		Z_PARAM_STR(lang)
	ZEND_PARSE_PARAMETERS_END();
	ZEPHIR_METHOD_GLOBALS_PTR = pecalloc(1, sizeof(zephir_method_globals), 0);
	zephir_memory_grow_stack(ZEPHIR_METHOD_GLOBALS_PTR, __func__);
	zephir_fetch_params(1, 1, 1, &word_param, &lang_param);
	zephir_get_strval(&word, word_param);
	if (!lang_param) {
		ZEPHIR_INIT_VAR(&lang);
		ZVAL_STRING(&lang, "english");
	} else {
		zephir_get_strval(&lang, lang_param);
	}
	ZEPHIR_INIT_VAR(&_0);
	ZVAL_STR(&_0, libstemmer_stem(Z_STR_P(&word), Z_STRVAL_P(&lang)));
	RETURN_CTOR(&_0);
}

