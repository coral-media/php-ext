#ifndef CORALMEDIA_ICU_BRIDGE_H
#define CORALMEDIA_ICU_BRIDGE_H

#include "php.h"

void icu_word_break(zend_string *text, const char *locale, zval *return_value);
void icu_sentence_break(zend_string *text, const char *locale, zval *return_value);
void icu_lowercase(zend_string *text, const char *locale, zval *return_value);

#endif
