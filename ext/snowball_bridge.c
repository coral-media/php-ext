#include "snowball_bridge.h"
#include "libstemmer/include/libstemmer.h"

zend_string *libstemmer_stem(zend_string *word, const char *lang)
{
    struct sb_stemmer *stemmer;
    const sb_symbol *out;
    int out_len;

    stemmer = sb_stemmer_new(lang, "UTF_8");
    if (!stemmer) {
        return NULL;
    }

    out = sb_stemmer_stem(stemmer, (const sb_symbol*) ZSTR_VAL(word), ZSTR_LEN(word));
    out_len = sb_stemmer_length(stemmer);

    zend_string *result = zend_string_init((char*) out, out_len, 0);
    sb_stemmer_delete(stemmer);

    return result;
}