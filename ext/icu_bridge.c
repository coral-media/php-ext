#include "icu_bridge.h"
#include <unicode/ubrk.h>
#include <unicode/ustring.h>
#include <unicode/utypes.h>
#include <unicode/utrans.h>

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

void icu_lowercase(zend_string *text, const char *locale, zval *return_value)
{
    // 1. Input validation
    if (!text || ZSTR_LEN(text) == 0) {
        ZVAL_EMPTY_STRING(return_value);
        return;
    }

    UErrorCode status = U_ZERO_ERROR;

    // 2. UTF-8 to UTF-16 conversion
    int32_t u16_len = 0;
    UChar *u16_text = NULL;

    // Pre-flight to get required buffer size
    u_strFromUTF8(NULL, 0, &u16_len, ZSTR_VAL(text), ZSTR_LEN(text), &status);
    if (status != U_BUFFER_OVERFLOW_ERROR && U_FAILURE(status)) {
        zend_value_error("icu_lowercase: UTF-8 conversion failed");
        return;
    }

    status = U_ZERO_ERROR;
    u16_text = (UChar*) emalloc(sizeof(UChar) * (u16_len + 1));
    u_strFromUTF8(u16_text, u16_len + 1, &u16_len, ZSTR_VAL(text), ZSTR_LEN(text), &status);

    if (U_FAILURE(status)) {
        efree(u16_text);
        zend_value_error("icu_lowercase: UTF-8 to UTF-16 conversion failed");
        return;
    }

    // 3. Apply lowercase transformation
    // Allocate buffer for result (same size should be sufficient for lowercase)
    UChar *u16_result = (UChar*) emalloc(sizeof(UChar) * (u16_len + 1));
    int32_t result_len = u_strToLower(u16_result, u16_len + 1, u16_text, u16_len, locale, &status);

    if (U_FAILURE(status)) {
        efree(u16_text);
        efree(u16_result);
        zend_value_error("icu_lowercase: Case conversion failed");
        return;
    }

    // 4. Convert UTF-16 result back to UTF-8
    int32_t u8_len = 0;
    u_strToUTF8(NULL, 0, &u8_len, u16_result, result_len, &status);

    if (status == U_BUFFER_OVERFLOW_ERROR) {
        status = U_ZERO_ERROR;
        char *u8_result = (char*) emalloc(u8_len + 1);
        u_strToUTF8(u8_result, u8_len + 1, &u8_len, u16_result, result_len, &status);

        if (U_SUCCESS(status)) {
            ZVAL_STRINGL(return_value, u8_result, u8_len);
        } else {
            ZVAL_EMPTY_STRING(return_value);
        }

        efree(u8_result);
    } else {
        ZVAL_EMPTY_STRING(return_value);
    }

    // 5. Cleanup
    efree(u16_text);
    efree(u16_result);
}

void icu_remove_diacritics(zend_string *text, zval *return_value)
{
    // 1. Input validation
    if (!text || ZSTR_LEN(text) == 0) {
        ZVAL_EMPTY_STRING(return_value);
        return;
    }

    UErrorCode status = U_ZERO_ERROR;

    // 2. UTF-8 to UTF-16 conversion
    int32_t u16_len = 0;
    UChar *u16_text = NULL;

    // Pre-flight to get required buffer size
    u_strFromUTF8(NULL, 0, &u16_len, ZSTR_VAL(text), ZSTR_LEN(text), &status);
    if (status != U_BUFFER_OVERFLOW_ERROR && U_FAILURE(status)) {
        zend_value_error("icu_remove_diacritics: UTF-8 conversion failed");
        return;
    }

    status = U_ZERO_ERROR;
    u16_text = (UChar*) emalloc(sizeof(UChar) * (u16_len + 1));
    u_strFromUTF8(u16_text, u16_len + 1, &u16_len, ZSTR_VAL(text), ZSTR_LEN(text), &status);

    if (U_FAILURE(status)) {
        efree(u16_text);
        zend_value_error("icu_remove_diacritics: UTF-8 to UTF-16 conversion failed");
        return;
    }

    // 3. Create transliterator
    // NFD = Decompose, remove nonspacing marks, NFC = Recompose
    UChar trans_id[] = {
        0x4E, 0x46, 0x44, 0x3B, 0x20,  // "NFD; "
        0x5B, 0x3A, 0x4E, 0x6F, 0x6E, 0x73, 0x70, 0x61, 0x63, 0x69, 0x6E, 0x67, 0x20, 0x4D, 0x61, 0x72, 0x6B, 0x3A, 0x5D, 0x20,  // "[[:Nonspacing Mark:]] "
        0x52, 0x65, 0x6D, 0x6F, 0x76, 0x65, 0x3B, 0x20,  // "Remove; "
        0x4E, 0x46, 0x43,  // "NFC"
        0x00
    };

    UTransliterator *trans = utrans_openU(trans_id, -1, UTRANS_FORWARD, NULL, 0, NULL, &status);
    if (U_FAILURE(status) || !trans) {
        efree(u16_text);
        zend_value_error("icu_remove_diacritics: Failed to create transliterator");
        return;
    }

    // 4. Apply transliteration
    // Allocate buffer with extra space for potential expansion
    int32_t capacity = u16_len * 2 + 10;
    UChar *u16_result = (UChar*) emalloc(sizeof(UChar) * capacity);
    u_strncpy(u16_result, u16_text, u16_len);
    u16_result[u16_len] = 0;

    int32_t result_len = u16_len;
    int32_t limit = u16_len;

    utrans_transUChars(trans, u16_result, &result_len, capacity, 0, &limit, &status);

    if (U_FAILURE(status)) {
        utrans_close(trans);
        efree(u16_text);
        efree(u16_result);
        zend_value_error("icu_remove_diacritics: Transliteration failed");
        return;
    }

    // 5. Convert UTF-16 result back to UTF-8
    int32_t u8_len = 0;
    u_strToUTF8(NULL, 0, &u8_len, u16_result, result_len, &status);

    if (status == U_BUFFER_OVERFLOW_ERROR) {
        status = U_ZERO_ERROR;
        char *u8_result = (char*) emalloc(u8_len + 1);
        u_strToUTF8(u8_result, u8_len + 1, &u8_len, u16_result, result_len, &status);

        if (U_SUCCESS(status)) {
            ZVAL_STRINGL(return_value, u8_result, u8_len);
        } else {
            ZVAL_EMPTY_STRING(return_value);
        }

        efree(u8_result);
    } else {
        ZVAL_EMPTY_STRING(return_value);
    }

    // 6. Cleanup
    utrans_close(trans);
    efree(u16_text);
    efree(u16_result);
}
