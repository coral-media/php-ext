namespace CoralMedia;

use CoralMedia\Text\Tokenizer\Icu;

class Text
{
    /**
     * Tokenize text into words using ICU word boundary analysis
     *
     * Supports multiple languages including English, Japanese, Chinese, Thai, etc.
     *
     * @param string text The text to tokenize
     * @param string locale The locale (default: "en_US")
     * @return array Array of word tokens
     */
    public static function wordBreak(string text, string locale = "en_US") -> array
    {
        return Icu::wordBreak(text, locale);
    }

    /**
     * Split text into sentences using ICU sentence boundary analysis
     *
     * @param string text The text to split
     * @param string locale The locale (default: "en_US")
     * @return array Array of sentences
     */
    public static function sentenceBreak(string text, string locale = "en_US") -> array
    {
        return Icu::sentenceBreak(text, locale);
    }

    /**
     * Convert text to lowercase using ICU locale-aware case mapping
     *
     * @param string text The text to convert
     * @param string locale The locale (default: "en_US")
     * @return string Lowercase text
     */
    public static function lowercase(string text, string locale = "en_US") -> string
    {
        return Icu::lowercase(text, locale);
    }

    /**
     * Remove diacritics (accents) from text
     *
     * @param string text The text to process
     * @return string Text with diacritics removed
     */
    public static function removeDiacritics(string text) -> string
    {
        return Icu::removeDiacritics(text);
    }
}
