#include "icu_bridge.h"
#include <unicode/ubrk.h>
#include <unicode/ustring.h>
#include <unicode/utypes.h>

void icu_word_break(zend_string *text, const char *locale, zval *return_value)
{
    // 1. Input validation
    if (!text || ZSTR_LEN(text) == 0) {
        array_init(return_value);
        return;
    }

    UErrorCode status = U_ZERO_ERROR;

    // 2. UTF-8 to UTF-16 conversion
    // ICU uses UTF-16 internally, PHP uses UTF-8
    int32_t u16_len = 0;
    UChar *u16_text = NULL;

    // Pre-flight to get required buffer size
    u_strFromUTF8(NULL, 0, &u16_len, ZSTR_VAL(text), ZSTR_LEN(text), &status);
    if (status != U_BUFFER_OVERFLOW_ERROR && U_FAILURE(status)) {
        zend_value_error("icu_word_break: UTF-8 conversion failed");
        return;
    }

    status = U_ZERO_ERROR;
    u16_text = (UChar*) emalloc(sizeof(UChar) * (u16_len + 1));
    u_strFromUTF8(u16_text, u16_len + 1, &u16_len, ZSTR_VAL(text), ZSTR_LEN(text), &status);

    if (U_FAILURE(status)) {
        efree(u16_text);
        zend_value_error("icu_word_break: UTF-8 to UTF-16 conversion failed");
        return;
    }

    // 3. Create ICU break iterator
    UBreakIterator *bi = ubrk_open(UBRK_WORD, locale, u16_text, u16_len, &status);
    if (U_FAILURE(status) || !bi) {
        efree(u16_text);
        zend_value_error("icu_word_break: Failed to create break iterator (invalid locale?)");
        return;
    }

    // 4. Iterate through boundaries and collect words
    array_init(return_value);

    int32_t start = ubrk_first(bi);
    int32_t end = ubrk_next(bi);

    while (end != UBRK_DONE) {
        // Check if this is a word (not whitespace/punctuation)
        int32_t rule_status = ubrk_getRuleStatus(bi);

        // UBRK_WORD_NONE = 0 (whitespace), other values = actual words
        if (rule_status != UBRK_WORD_NONE) {
            // Extract the word segment
            int32_t word_len_u16 = end - start;
            UChar *word_u16 = u16_text + start;

            // Convert UTF-16 word back to UTF-8
            int32_t word_len_u8 = 0;
            u_strToUTF8(NULL, 0, &word_len_u8, word_u16, word_len_u16, &status);

            if (status == U_BUFFER_OVERFLOW_ERROR) {
                status = U_ZERO_ERROR;
                char *word_u8 = (char*) emalloc(word_len_u8 + 1);
                u_strToUTF8(word_u8, word_len_u8 + 1, &word_len_u8, word_u16, word_len_u16, &status);

                if (U_SUCCESS(status)) {
                    add_next_index_stringl(return_value, word_u8, word_len_u8);
                }

                efree(word_u8);
                status = U_ZERO_ERROR;
            }
        }

        start = end;
        end = ubrk_next(bi);
    }

    // 5. Cleanup
    ubrk_close(bi);
    efree(u16_text);
}

void icu_sentence_break(zend_string *text, const char *locale, zval *return_value)
{
    // 1. Input validation
    if (!text || ZSTR_LEN(text) == 0) {
        array_init(return_value);
        return;
    }

    UErrorCode status = U_ZERO_ERROR;

    // 2. UTF-8 to UTF-16 conversion
    int32_t u16_len = 0;
    UChar *u16_text = NULL;

    u_strFromUTF8(NULL, 0, &u16_len, ZSTR_VAL(text), ZSTR_LEN(text), &status);
    if (status != U_BUFFER_OVERFLOW_ERROR && U_FAILURE(status)) {
        zend_value_error("icu_sentence_break: UTF-8 conversion failed");
        return;
    }

    status = U_ZERO_ERROR;
    u16_text = (UChar*) emalloc(sizeof(UChar) * (u16_len + 1));
    u_strFromUTF8(u16_text, u16_len + 1, &u16_len, ZSTR_VAL(text), ZSTR_LEN(text), &status);

    if (U_FAILURE(status)) {
        efree(u16_text);
        zend_value_error("icu_sentence_break: UTF-8 to UTF-16 conversion failed");
        return;
    }

    // 3. Create sentence break iterator
    UBreakIterator *bi = ubrk_open(UBRK_SENTENCE, locale, u16_text, u16_len, &status);
    if (U_FAILURE(status) || !bi) {
        efree(u16_text);
        zend_value_error("icu_sentence_break: Failed to create break iterator");
        return;
    }

    // 4. Collect sentences
    array_init(return_value);

    int32_t start = ubrk_first(bi);
    int32_t end = ubrk_next(bi);

    while (end != UBRK_DONE) {
        int32_t sent_len_u16 = end - start;
        UChar *sent_u16 = u16_text + start;

        // Convert to UTF-8
        int32_t sent_len_u8 = 0;
        u_strToUTF8(NULL, 0, &sent_len_u8, sent_u16, sent_len_u16, &status);

        if (status == U_BUFFER_OVERFLOW_ERROR) {
            status = U_ZERO_ERROR;
            char *sent_u8 = (char*) emalloc(sent_len_u8 + 1);
            u_strToUTF8(sent_u8, sent_len_u8 + 1, &sent_len_u8, sent_u16, sent_len_u16, &status);

            if (U_SUCCESS(status)) {
                add_next_index_stringl(return_value, sent_u8, sent_len_u8);
            }

            efree(sent_u8);
            status = U_ZERO_ERROR;
        }

        start = end;
        end = ubrk_next(bi);
    }

    // 5. Cleanup
    ubrk_close(bi);
    efree(u16_text);
}
