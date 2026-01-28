
extern ZEPHIR_API zend_class_entry *coralmedia_stemmer_snowball_ce;

ZEPHIR_INIT_CLASS(CoralMedia_Stemmer_Snowball);

PHP_METHOD(CoralMedia_Stemmer_Snowball, stem);

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_coralmedia_stemmer_snowball_stem, 0, 1, IS_STRING, 1)
	ZEND_ARG_TYPE_INFO(0, word, IS_STRING, 0)
	ZEND_ARG_TYPE_INFO(0, lang, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEPHIR_INIT_FUNCS(coralmedia_stemmer_snowball_method_entry) {
	PHP_ME(CoralMedia_Stemmer_Snowball, stem, arginfo_coralmedia_stemmer_snowball_stem, ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
	PHP_FE_END
};
