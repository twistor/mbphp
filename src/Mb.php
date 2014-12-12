<?php

/**
 * @file
 * Contains \MbPhp\Mb.
 */

namespace MbPhp;

/**
 * mbstring compatible functions.
 */
class Mb
{
    /**
     * A static cache of encoders keyed by encoding.
     *
     * @var \MbPhp\Encoder\Encoder[]
     */
    protected static $encoders = array();

    /**
     * The default encoding to use.
     *
     * @var string
     */
    protected static $internalEncoding = 'utf8';

    /**
     * Checks if the string is valid for the specified encoding.
     *
     * @param string $string   The byte stream to check.
     * @param string $encoding The expected encoding.
     *
     * @return bool Returns true on success or false on failure.
     */
    public static function checkEncoding($string, $encoding = null)
    {
        return $string === static::convertEncoding($string, $encoding, $encoding);
    }

    /**
     * Converts character encoding.
     *
     * @param string $string        The string being encoded.
     * @param string $to_encoding   The type of encoding that string is being converted to.
     * @param string $from_encoding If from_encoding is not specified, the internal encoding will be used.
     *
     * @return string The encoded string.
     */
    public static function convertEncoding($string, $to_encoding, $from_encoding = null)
    {
        if ($from_encoding === null) {
            $from_encoding = static::$internalEncoding;
        }

        $codepoints = static::getEncoder($from_encoding)->decode($string);

        return static::getEncoder($to_encoding)->encode($codepoints);
    }

    /**
     * Sets/Gets internal character encoding.
     *
     * @param string $encoding The default character encoding for string functions.
     *
     * @return bool|string
     */
    public static function internalEncoding($encoding = null)
    {
        if ($encoding === null) {
            return static::$internalEncoding;
        }

        $encoding = static::normalize($encoding);

        if (isset(static::$encoderClass[$encoding])) {
            static::$internalEncoding = $encoding;

            return true;
        }

        return false;
    }

    public static function strlen($string, $encoding = null)
    {
        if ($encoding === null) {
            $encoding = static::$internalEncoding;
        }

        return count(static::getEncoder($encoding)->decode($string));
    }

    public static function strpos($haystack, $needle, $offset = 0, $encoding = null)
    {
        if ($encoding === null) {
            $encoding = static::$internalEncoding;
        }

        $encoder = static::getEncoder($encoding);

        $haystack = $encoder->decode($haystack);
        $needle = $encoder->decode($needle);

        $needleLen = count($needle);

        // Optimize for when $needle is a single character.
        if ($needleLen === 1) {
            return array_search($needle[0], $haystack, true);
        }

        $haystackLen = count($haystack) - $needleLen + 1;

        for ($i = $offset; $i < $haystackLen; $i++) {
            if (array_slice($haystack, $i, $needleLen) === $needle) {
                return $i;
            }
        }

        return false;
    }

    public static function strrpos($haystack, $needle, $offset = 0, $encoding = null)
    {
        if ($encoding === null) {
            $encoding = static::$internalEncoding;
        }

        $encoder = static::getEncoder($encoding);

        $haystack = $encoder->decode($haystack);
        $needle = $encoder->decode($needle);

        $needleLen = count($needle);

        // Optimize for when $needle is a single character.
        if ($needleLen === 1) {
            $needle = $needle[0];

            // @todo Test memory usage of array_reverse(). My thought is that
            // copying the array could be bad, but not sure. Walking the array
            // in reverse should be pretty performant.
            for ($i = count($haystack) - 1 + $offset; $i >= 0; $i--) {
                if ($haystack[$i] === $needle) {
                    return $i;
                }
            }

            return false;
        }

        $haystackLen = count($haystack) - $needleLen + 1;

        for ($i = $haystackLen + $offset; $i >= 0; $i--) {
            if (array_slice($haystack, $i, $needleLen) === $needle) {
                return $i;
            }
        }

        return false;
    }

    /**
     * Makes a string lowercase.
     *
     * @param string $string   The string being lowercased.
     * @param string $encoding The character encoding. Defaults to the internal encoding.
     *
     * @return string $string with all alphabetic characters converted to lowercase.
     */
    public static function strtolower($string, $encoding = null)
    {
        // @todo Figure out if we should cache this. Needs profiling include
        // time vs. memory usage.
        // @todo Handle special casing http://www.unicode.org/faq/casemap_charprop.html
        $map = require Index::getDir().'/upper_to_lower.php';

        return static::flipCase($string, $encoding, $map);
    }

    /**
     * Makes a string uppercase.
     *
     * @param string $string   The string being uppercased.
     * @param string $encoding The character encoding. Defaults to the internal encoding.
     *
     * @return string $string with all alphabetic characters converted to uppercase.
     */
    public static function strtoupper($string, $encoding = null)
    {
        $map = require Index::getDir().'/lower_to_upper.php';

        return static::flipCase($string, $encoding, $map);
    }

    /**
     * Counts the number of substring occurrences.
     *
     * @param string $haystack The string being checked.
     * @param string $needle   The string being found.
     * @param string $encoding The character encoding. Defaults to the internal encoding.
     *
     * @return int The number of times the needle substring occurs in the haystack string.
     */
    public static function substrCount($haystack, $needle, $encoding = null)
    {
        if ($encoding === null) {
            $encoding = static::$internalEncoding;
        }

        $encoder = static::getEncoder($encoding);

        $haystack = $encoder->decode($haystack);
        $needle = $encoder->decode($needle);

        $needleLen = count($needle);

        // Optimize the case when needle is one character.
        if ($needleLen === 1) {
            return count(array_keys($haystack, $needle[0], true));
        }

        $haystackLen = count($haystack) - $needleLen + 1;

        $count = 0;

        for ($i = 0; $i < $haystackLen; $i++) {
            if (array_slice($haystack, $i, $needleLen) === $needle) {
                $count++;
            }
        }

        return $count;
    }

    public static function substr($string, $start, $length = null, $encoding = null)
    {
        if ($encoding === null) {
            $encoding = static::$internalEncoding;
        }

        $encoder = static::getEncoder($encoding);

        $codepoints = array_slice($encoder->decode($string), $start, $length);

        return $encoder->encode($codepoints);
    }

    public static function registerEncoder($encoding, $class)
    {
        static::$encoderClass[static::normalize($encoding)] = $class;
    }

    /**
     * Normalizes an encoding.
     *
     * @param string $encoding The name of the encoding.
     *
     * @return string The normalized encoding.
     */
    public static function normalize($encoding)
    {
        $encoding = preg_replace('/[^a-z0-9]/', '', strtolower($encoding));
        if (isset(static::$normalizations[$encoding])) {
            return static::$normalizations[$encoding];
        }
        return $encoding;
    }

    protected static function getEncoder($encoding)
    {
        $encoding = static::normalize($encoding);

        if (!isset(static::$encoders[$encoding])) {
            $class = isset(static::$encoderClass[$encoding]) ? static::$encoderClass[$encoding] : 'MbPhp\Encoder\Noop';
            static::$encoders[$encoding] = new $class($encoding);
        }

        return static::$encoders[$encoding];
    }

    protected static function flipCase($string, $encoding, array $map)
    {
        if ($encoding === null) {
            $encoding = static::$internalEncoding;
        }

        $encoder = static::getEncoder($encoding);

        $codepoints = $encoder->decode($string);

        foreach ($codepoints as &$codepoint) {
            if (isset($map[$codepoint])) {
                $codepoint = $map[$codepoint];
            }
        }

        return $encoder->encode($codepoints);
    }

    protected static $encoderClass = array(
        'gb18030'      => 'MbPhp\Encoder\Gb18030',

        'utf8'         => 'MbPhp\Encoder\Utf8',

        'ibm866'       => 'MbPhp\Encoder\SingleByte',
        'iso88592'     => 'MbPhp\Encoder\SingleByte',
        'iso88593'     => 'MbPhp\Encoder\SingleByte',
        'iso88594'     => 'MbPhp\Encoder\SingleByte',
        'iso88595'     => 'MbPhp\Encoder\SingleByte',
        'iso88596'     => 'MbPhp\Encoder\SingleByte',
        'iso88597'     => 'MbPhp\Encoder\SingleByte',
        'iso88598'     => 'MbPhp\Encoder\SingleByte',
        'iso885910'    => 'MbPhp\Encoder\SingleByte',
        'iso885913'    => 'MbPhp\Encoder\SingleByte',
        'iso885914'    => 'MbPhp\Encoder\SingleByte',
        'iso885915'    => 'MbPhp\Encoder\SingleByte',
        'iso885916'    => 'MbPhp\Encoder\SingleByte',
        'koi8r'        => 'MbPhp\Encoder\SingleByte',
        'koi8u'        => 'MbPhp\Encoder\SingleByte',
        'macintosh'    => 'MbPhp\Encoder\SingleByte',
        'windows874'   => 'MbPhp\Encoder\SingleByte',
        'windows1250'  => 'MbPhp\Encoder\SingleByte',
        'windows1251'  => 'MbPhp\Encoder\SingleByte',
        'windows1252'  => 'MbPhp\Encoder\SingleByte',
        'windows1253'  => 'MbPhp\Encoder\SingleByte',
        'windows1254'  => 'MbPhp\Encoder\SingleByte',
        'windows1255'  => 'MbPhp\Encoder\SingleByte',
        'windows1256'  => 'MbPhp\Encoder\SingleByte',
        'windows1257'  => 'MbPhp\Encoder\SingleByte',
        'xmaccyrillic' => 'MbPhp\Encoder\SingleByte',
    );

    /**
     * Normalizations.
     *
     * @var array
     */
    protected static $normalizations = array(
        'unicode11utf8' => 'utf8',

        '866' => 'ibm866',
        'cp866' => 'ibm866',
        'csibm866' => 'ibm866',

        'csisolatin2' => 'iso88592',
        'isoir101' => 'iso88592',
        'iso885921987' => 'iso88592',
        'l2' => 'iso88592',
        'latin2' => 'iso88592',

        'csisolatin3' => 'iso88593',
        'isoir109' => 'iso88593',
        'iso885931988' => 'iso88593',
        'l3' => 'iso88593',
        'latin3' => 'iso88593',

        'csisolatin4' => 'iso88594',
        'isoir110' => 'iso88594',
        'iso885941988' => 'iso88594',
        'l4' => 'iso88594',
        'latin4' => 'iso88594',

        'csisolatincyrillic' => 'iso88595',
        'cyrillic' => 'iso88595',
        'isoir144' => 'iso88595',
        'iso885951988' => 'iso88595',

        'arabic' => 'iso88596',
        'asmo708' => 'iso88596',
        'csiso88596e' => 'iso88596',
        'csiso88596i' => 'iso88596',
        'csisolatinarabic' => 'iso88596',
        'ecma114' => 'iso88596',
        'iso88596e' => 'iso88596',
        'iso88596i' => 'iso88596',
        'isoir127' => 'iso88596',
        'iso885961987' => 'iso88596',

        'csisolatingreek' => 'iso88597',
        'ecma118' => 'iso88597',
        'elot928' => 'iso88597',
        'greek' => 'iso88597',
        'greek8' => 'iso88597',
        'isoir126' => 'iso88597',
        'iso885971987' => 'iso88597',
        'suneugreek' => 'iso88597',

        'csiso88598e' => 'iso88598',
        'csisolatinhebrew' => 'iso88598',
        'hebrew' => 'iso88598',
        'iso88598e' => 'iso88598',
        'isoir138' => 'iso88598',
        'iso885981988' => 'iso88598',
        'visual' => 'iso88598',

        'csiso88598i' => 'iso88598i',
        'logical' => 'iso88598i',

        'csisolatin6' => 'iso885910',
        'isoir157' => 'iso885910',
        'l6' => 'iso885910',
        'latin6' => 'iso885910',

        // 'iso885913' => 'iso885913',

        // 'iso885914' => 'iso885914',

        'csisolatin9' => 'iso885915',
        'l9' => 'iso885915',

        // 'iso885916' => 'iso-8859-16',

        'cskoi8r' => 'koi8r',
        'koi' => 'koi8r',
        'koi8' => 'koi8r',

        // 'koi8u' => 'koi8-u',

        'csmacintosh' => 'macintosh',
        'mac' => 'macintosh',
        'xmacroman' => 'macintosh',

        'dos874' => 'windows874',
        'iso885911' => 'windows874',
        'tis620' => 'windows874',

        'cp1250' => 'windows1250',
        'xcp1250' => 'windows1250',

        'cp1251' => 'windows1251',
        'xcp1251' => 'windows1251',

        'ascii' => 'windows1252',
        'usascii' => 'windows1252',
        'ansix341968' => 'windows1252',
        'cp1252' => 'windows1252',
        'cp819' => 'windows1252',
        'csisolatin1' => 'windows1252',
        'ibm819' => 'windows1252',
        'iso88591' => 'windows1252',
        'isoir100' => 'windows1252',
        'iso885911987' => 'windows1252',
        'l1' => 'windows1252',
        'latin1' => 'windows1252',
        'xcp1252' => 'windows1252',

        'cp1253' => 'windows1253',
        'xcp1253' => 'windows1253',

        'cp1254' => 'windows1254',
        'csisolatin5' => 'windows1254',
        'iso88599' => 'windows1254',
        'isoir148' => 'windows1254',
        'iso885991989' => 'windows1254',
        'l5' => 'windows1254',
        'latin5' => 'windows1254',
        'xcp1254' => 'windows1254',

        'cp1255' => 'windows1255',
        'xcp1255' => 'windows1255',

        'cp1256' => 'windows1256',
        'xcp1256' => 'windows1256',

        'cp1257' => 'windows1257',
        'xcp1257' => 'windows1257',

        'cp1258' => 'windows1258',
        'xcp1258' => 'windows1258',

        'xmacukrainian' => 'xmaccyrillic',

        'chinese' => 'gbk',
        'csgb2312' => 'gbk',
        'csiso58gb231280' => 'gbk',
        'gb2312' => 'gbk',
        'gb231280' => 'gbk',
        'isoir58' => 'gbk',
        'xgbk' => 'gbk',

        // 'gb18030' => 'gb18030',

        'big5hkscs' => 'big5',
        'cnbig5' => 'big5',
        'csbig5' => 'big5',
        'xxbig5' => 'big5',

        'cseucpkdfmtjapanese' => 'eucjp',
        'xeucjp' => 'eucjp',

        'csiso2022jp' => 'iso2022jp',

        'csshiftjis' => 'shiftjis',
        'mskanji' => 'shiftjis',
        'sjis' => 'shiftjis',
        'windows31j' => 'shiftjis',
        'xsjis' => 'shiftjis',

        'cseuckr' => 'euckr',
        'csksc56011987' => 'euckr',
        'isoir149' => 'euckr',
        'korean' => 'euckr',
        'ksc56011987' => 'euckr',
        'ksc5601' => 'euckr',
        'windows949' => 'euckr',

        'utf32' => 'utf32le',

        // 'utf32be' => 'utf-32be',
    );
}
