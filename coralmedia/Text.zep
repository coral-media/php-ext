namespace CoralMedia;

use CoralMedia\Text\Tokenizer\Icu;
use CoralMedia\Stemmer\Snowball;

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

    /**
     * Extract term frequency from text
     *
     * Returns an associative array mapping terms to their frequencies.
     *
     * Options:
     * - locale: string (default "en_US") - Locale for tokenization
     * - normalize: bool (default false) - Normalize frequencies by total term count
     * - lowercase: bool (default true) - Convert terms to lowercase
     * - remove_diacritics: bool (default false) - Remove diacritics from terms
     * - stem: bool (default false) - Apply stemming to terms
     * - stem_language: string (default "english") - Language for stemming
     *
     * @param string text The text to analyze
     * @param array options Configuration options
     * @return array Associative array of term => frequency
     */
    public static function termFrequency(string text, array options = []) -> array
    {
        var locale, normalize, toLowercase, removeDiacritics, applyStem, stemLanguage;
        var tokens, counts, term, total, processedTokens, stemmedTerm;
        var count, key;
        double normalizedValue;

        // Get options with defaults
        if fetch locale, options["locale"] {
            // locale is set
        } else {
            let locale = "en_US";
        }

        if fetch normalize, options["normalize"] {
            // normalize is set
        } else {
            let normalize = false;
        }

        if fetch toLowercase, options["lowercase"] {
            // toLowercase is set
        } else {
            let toLowercase = true;
        }

        if fetch removeDiacritics, options["remove_diacritics"] {
            // removeDiacritics is set
        } else {
            let removeDiacritics = false;
        }

        if fetch applyStem, options["stem"] {
            // applyStem is set
        } else {
            let applyStem = false;
        }

        if fetch stemLanguage, options["stem_language"] {
            // stemLanguage is set
        } else {
            let stemLanguage = "english";
        }

        // Tokenize text
        let tokens = self::wordBreak(text, locale);

        // Process tokens
        let processedTokens = [];
        for term in tokens {
            if removeDiacritics {
                let term = self::removeDiacritics(term);
            }
            if toLowercase {
                let term = self::lowercase(term, locale);
            }
            if applyStem {
                let stemmedTerm = Snowball::stem(term, stemLanguage);
                if stemmedTerm !== null {
                    let term = stemmedTerm;
                }
            }
            let processedTokens[] = term;
        }

        // Count frequencies
        let counts = [];
        for term in processedTokens {
            if fetch count, counts[term] {
                let counts[term] = count + 1;
            } else {
                let counts[term] = 1;
            }
        }

        // Normalize if requested
        if normalize {
            let total = count(processedTokens);
            if total > 0 {
                var normalizedCounts = [];
                for key, count in counts {
                    let normalizedValue = (double) count / (double) total;
                    let normalizedCounts[key] = normalizedValue;
                }
                return normalizedCounts;
            }
        }

        return counts;
    }

    /**
     * Calculate Inverse Document Frequency (IDF) from a corpus of documents
     *
     * IDF measures how important a term is across a collection of documents.
     * Terms that appear in many documents get lower scores, rare terms get higher scores.
     *
     * Formula: log(N / df) or log((N + 1) / (df + 1)) + 1 if smooth=true
     * where N = total documents, df = document frequency (documents containing term)
     *
     * Options:
     * - locale: string (default "en_US") - Locale for tokenization
     * - lowercase: bool (default true) - Convert terms to lowercase
     * - remove_diacritics: bool (default false) - Remove diacritics from terms
     * - stem: bool (default false) - Apply stemming to terms
     * - stem_language: string (default "english") - Language for stemming
     * - smooth: bool (default true) - Use smooth IDF to prevent division by zero
     *
     * @param array documents Array of document strings
     * @param array options Configuration options
     * @return array Associative array of term => IDF score
     */
    public static function idf(array documents, array options = []) -> array
    {
        var locale, toLowercase, removeDiacritics, applyStem, stemLanguage, smooth;
        var doc, tokens, term, documentFrequency, stemmedTerm;
        var termsSeen, numDocuments, idfScores, key, df;
        double idfScore;

        // Get options with defaults
        if fetch locale, options["locale"] {
            // locale is set
        } else {
            let locale = "en_US";
        }

        if fetch toLowercase, options["lowercase"] {
            // toLowercase is set
        } else {
            let toLowercase = true;
        }

        if fetch removeDiacritics, options["remove_diacritics"] {
            // removeDiacritics is set
        } else {
            let removeDiacritics = false;
        }

        if fetch applyStem, options["stem"] {
            // applyStem is set
        } else {
            let applyStem = false;
        }

        if fetch stemLanguage, options["stem_language"] {
            // stemLanguage is set
        } else {
            let stemLanguage = "english";
        }

        if fetch smooth, options["smooth"] {
            // smooth is set
        } else {
            let smooth = true;
        }

        // Count document frequency (number of documents containing each term)
        let documentFrequency = [];
        let numDocuments = count(documents);

        for doc in documents {
            // Get unique terms in this document
            let tokens = self::wordBreak(doc, locale);
            let termsSeen = [];

            for term in tokens {
                // Process term
                if removeDiacritics {
                    let term = self::removeDiacritics(term);
                }
                if toLowercase {
                    let term = self::lowercase(term, locale);
                }
                if applyStem {
                    let stemmedTerm = Snowball::stem(term, stemLanguage);
                    if stemmedTerm !== null {
                        let term = stemmedTerm;
                    }
                }

                // Mark term as seen in this document (only count once per document)
                let termsSeen[term] = true;
            }

            // Increment document frequency for each unique term
            for key, _ in termsSeen {
                if fetch df, documentFrequency[key] {
                    let documentFrequency[key] = df + 1;
                } else {
                    let documentFrequency[key] = 1;
                }
            }
        }

        // Calculate IDF scores
        let idfScores = [];
        var tmpScore;
        for key, df in documentFrequency {
            if smooth {
                // Smooth IDF: log((N + 1) / (df + 1)) + 1
                let tmpScore = log((double) (numDocuments + 1) / (double) (df + 1));
                let idfScore = (double) tmpScore + 1.0;
            } else {
                // Standard IDF: log(N / df)
                let tmpScore = log((double) numDocuments / (double) df);
                let idfScore = (double) tmpScore;
            }
            let idfScores[key] = idfScore;
        }

        return idfScores;
    }

    /**
     * Calculate TF-IDF (Term Frequency-Inverse Document Frequency) scores
     *
     * TF-IDF is a numerical statistic that reflects how important a word is
     * to a document in a collection of documents.
     *
     * Options:
     * - locale: string (default "en_US") - Locale for tokenization
     * - normalize: bool (default false) - Normalize TF before multiplication
     * - lowercase: bool (default true) - Convert terms to lowercase
     * - remove_diacritics: bool (default false) - Remove diacritics from terms
     * - stem: bool (default false) - Apply stemming to terms
     * - stem_language: string (default "english") - Language for stemming
     *
     * @param string document The document to analyze
     * @param array idfScores IDF scores from idf() method
     * @param array options Configuration options
     * @return array Associative array of term => TF-IDF score
     */
    public static function tfidf(string document, array idfScores, array options = []) -> array
    {
        var termFrequencies, tfidfScores, term, tf, idf;
        double score;

        // Calculate term frequencies for this document
        let termFrequencies = self::termFrequency(document, options);

        // Multiply TF by IDF
        let tfidfScores = [];
        for term, tf in termFrequencies {
            if fetch idf, idfScores[term] {
                let score = (double) tf * idf;
                let tfidfScores[term] = score;
            } else {
                // Term not in IDF dictionary (not seen in corpus)
                // Assign score of 0
                let tfidfScores[term] = 0.0;
            }
        }

        return tfidfScores;
    }
}
