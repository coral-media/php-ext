namespace CoralMedia\Text\Tokenizer;

class Icu
{
    /**
     * Break text into words using ICU word boundary analysis
     *
     * @param string text The text to tokenize
     * @param string locale The locale (e.g., "en_US", "ja_JP", "th_TH")
     * @return array Array of words
     */
    public static function wordBreak(string text, string locale = "en_US") -> array
    {
        // This call will be intercepted by the optimizer
        return icu_word_break(text, locale);
    }

    /**
     * Break text into sentences using ICU sentence boundary analysis
     *
     * @param string text The text to tokenize
     * @param string locale The locale (e.g., "en_US", "ja_JP")
     * @return array Array of sentences
     */
    public static function sentenceBreak(string text, string locale = "en_US") -> array
    {
        // This call will be intercepted by the optimizer
        return icu_sentence_break(text, locale);
    }

    /**
     * Convert text to lowercase using ICU locale-aware case mapping
     *
     * @param string text The text to convert
     * @param string locale The locale (e.g., "en_US", "tr_TR")
     * @return string Lowercase text
     */
    public static function lowercase(string text, string locale = "en_US") -> string
    {
        // This call will be intercepted by the optimizer
        return icu_lowercase(text, locale);
    }

    /**
     * Remove diacritics (accents) from text
     *
     * Converts characters like café → cafe, naïve → naive
     *
     * @param string text The text to process
     * @return string Text with diacritics removed
     */
    public static function removeDiacritics(string text) -> string
    {
        // This call will be intercepted by the optimizer
        return icu_remove_diacritics(text);
    }
}
