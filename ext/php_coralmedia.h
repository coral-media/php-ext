
/* This file was generated automatically by Zephir do not modify it! */

#ifndef PHP_CORALMEDIA_H
#define PHP_CORALMEDIA_H 1

#ifdef PHP_WIN32
#define ZEPHIR_RELEASE 1
#endif

#include "kernel/globals.h"

#define PHP_CORALMEDIA_NAME        "coralmedia"
#define PHP_CORALMEDIA_VERSION     "0.0.1"
#define PHP_CORALMEDIA_EXTNAME     "coralmedia"
#define PHP_CORALMEDIA_AUTHOR      "Coral Media"
#define PHP_CORALMEDIA_ZEPVERSION  "0.19.0-$Id$"
#define PHP_CORALMEDIA_DESCRIPTION ""



ZEND_BEGIN_MODULE_GLOBALS(coralmedia)

	int initialized;

	/** Function cache */
	HashTable *fcache;

	zephir_fcall_cache_entry *scache[ZEPHIR_MAX_CACHE_SLOTS];

	/* Cache enabled */
	unsigned int cache_enabled;

	/* Max recursion control */
	unsigned int recursive_lock;

	
ZEND_END_MODULE_GLOBALS(coralmedia)

#ifdef ZTS
#include "TSRM.h"
#endif

ZEND_EXTERN_MODULE_GLOBALS(coralmedia)

#ifdef ZTS
	#define ZEPHIR_GLOBAL(v) ZEND_MODULE_GLOBALS_ACCESSOR(coralmedia, v)
#else
	#define ZEPHIR_GLOBAL(v) (coralmedia_globals.v)
#endif

#ifdef ZTS
	ZEND_TSRMLS_CACHE_EXTERN()
	#define ZEPHIR_VGLOBAL ((zend_coralmedia_globals *) (*((void ***) tsrm_get_ls_cache()))[TSRM_UNSHUFFLE_RSRC_ID(coralmedia_globals_id)])
#else
	#define ZEPHIR_VGLOBAL &(coralmedia_globals)
#endif

#define ZEPHIR_API ZEND_API

#define zephir_globals_def coralmedia_globals
#define zend_zephir_globals_def zend_coralmedia_globals

extern zend_module_entry coralmedia_module_entry;
#define phpext_coralmedia_ptr &coralmedia_module_entry

#endif
