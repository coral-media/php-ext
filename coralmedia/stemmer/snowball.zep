namespace CoralMedia\Stemmer;

class Snowball
{
    public static function stem(string word, string lang = "english") -> string | null
    {
        return libstemmer_stem(word, lang);
    }
}