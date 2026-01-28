namespace CoralMedia\Stemmer;

class Snowball
{
    public static function stem(string word, string lang = "english") -> string | null
    {
        // This call is intercepted by LibstemmerStemOptimizer
        return libstemmer_stem(word, lang);
    }
}