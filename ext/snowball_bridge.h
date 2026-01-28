#ifndef CORALMEDIA_SNOWBALL_BRIDGE_H
#define CORALMEDIA_SNOWBALL_BRIDGE_H

#include "php.h"

zend_string *libstemmer_stem(zend_string *word, const char *lang);

#endif