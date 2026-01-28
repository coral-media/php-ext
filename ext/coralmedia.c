
/* This file was generated automatically by Zephir do not modify it! */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <php.h>

#include "php_ext.h"
#include "coralmedia.h"

#include <ext/standard/info.h>

#include <Zend/zend_operators.h>
#include <Zend/zend_exceptions.h>
#include <Zend/zend_interfaces.h>

#include "kernel/globals.h"
#include "kernel/main.h"
#include "kernel/fcall.h"
#include "kernel/memory.h"



zend_class_entry *coralmedia_stemmer_snowball_ce;

ZEND_DECLARE_MODULE_GLOBALS(coralmedia)

PHP_INI_BEGIN()
	
PHP_INI_END()

static PHP_MINIT_FUNCTION(coralmedia)
{
	REGISTER_INI_ENTRIES();
	zephir_module_init();
	ZEPHIR_INIT(CoralMedia_Stemmer_Snowball);
	
	return SUCCESS;
}

#ifndef ZEPHIR_RELEASE
static PHP_MSHUTDOWN_FUNCTION(coralmedia)
{
	
	zephir_deinitialize_memory();
	UNREGISTER_INI_ENTRIES();
	return SUCCESS;
}
#endif

/**
 * Initialize globals on each request or each thread started
 */
static void php_zephir_init_globals(zend_coralmedia_globals *coralmedia_globals)
{
	coralmedia_globals->initialized = 0;

	/* Cache Enabled */
	coralmedia_globals->cache_enabled = 1;

	/* Recursive Lock */
	coralmedia_globals->recursive_lock = 0;

	/* Static cache */
	memset(coralmedia_globals->scache, '\0', sizeof(zephir_fcall_cache_entry*) * ZEPHIR_MAX_CACHE_SLOTS);

	
	
}

/**
 * Initialize globals only on each thread started
 */
static void php_zephir_init_module_globals(zend_coralmedia_globals *coralmedia_globals)
{
	
}

static PHP_RINIT_FUNCTION(coralmedia)
{
	zend_coralmedia_globals *coralmedia_globals_ptr;
	coralmedia_globals_ptr = ZEPHIR_VGLOBAL;

	php_zephir_init_globals(coralmedia_globals_ptr);
	zephir_initialize_memory(coralmedia_globals_ptr);

	
	return SUCCESS;
}

static PHP_RSHUTDOWN_FUNCTION(coralmedia)
{
	
	zephir_deinitialize_memory();
	return SUCCESS;
}



static PHP_MINFO_FUNCTION(coralmedia)
{
	php_info_print_box_start(0);
	php_printf("%s", PHP_CORALMEDIA_DESCRIPTION);
	php_info_print_box_end();

	php_info_print_table_start();
	php_info_print_table_header(2, PHP_CORALMEDIA_NAME, "enabled");
	php_info_print_table_row(2, "Author", PHP_CORALMEDIA_AUTHOR);
	php_info_print_table_row(2, "Version", PHP_CORALMEDIA_VERSION);
	php_info_print_table_row(2, "Build Date", __DATE__ " " __TIME__ );
	php_info_print_table_row(2, "Powered by Zephir", "Version " PHP_CORALMEDIA_ZEPVERSION);
	php_info_print_table_end();
	
	DISPLAY_INI_ENTRIES();
}

static PHP_GINIT_FUNCTION(coralmedia)
{
#if defined(COMPILE_DL_CORALMEDIA) && defined(ZTS)
	ZEND_TSRMLS_CACHE_UPDATE();
#endif

	php_zephir_init_globals(coralmedia_globals);
	php_zephir_init_module_globals(coralmedia_globals);
}

static PHP_GSHUTDOWN_FUNCTION(coralmedia)
{
	
}


zend_function_entry php_coralmedia_functions[] = {
	ZEND_FE_END

};

static const zend_module_dep php_coralmedia_deps[] = {
	
	ZEND_MOD_END
};

zend_module_entry coralmedia_module_entry = {
	STANDARD_MODULE_HEADER_EX,
	NULL,
	php_coralmedia_deps,
	PHP_CORALMEDIA_EXTNAME,
	php_coralmedia_functions,
	PHP_MINIT(coralmedia),
#ifndef ZEPHIR_RELEASE
	PHP_MSHUTDOWN(coralmedia),
#else
	NULL,
#endif
	PHP_RINIT(coralmedia),
	PHP_RSHUTDOWN(coralmedia),
	PHP_MINFO(coralmedia),
	PHP_CORALMEDIA_VERSION,
	ZEND_MODULE_GLOBALS(coralmedia),
	PHP_GINIT(coralmedia),
	PHP_GSHUTDOWN(coralmedia),
#ifdef ZEPHIR_POST_REQUEST
	PHP_PRSHUTDOWN(coralmedia),
#else
	NULL,
#endif
	STANDARD_MODULE_PROPERTIES_EX
};

/* implement standard "stub" routine to introduce ourselves to Zend */
#ifdef COMPILE_DL_CORALMEDIA
# ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
# endif
ZEND_GET_MODULE(coralmedia)
#endif
