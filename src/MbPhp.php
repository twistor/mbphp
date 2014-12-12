<?php

/**
 * @file
 * Contains \MbPhp\MbPhp.
 */

namespace MbPhp;

use MbPhp\Index;

/**
 * mbstring compatible functions.
 */
class MbPhp {

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
    protected static $internalEncoding;

    /**
     * Checks if the string is valid for the specified encoding.
     *
     * @param string $string The byte stream to check.
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
     * @param string $string The string being encoded.
     * @param string $to_encoding The type of encoding that string is being converted to.
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
            return true
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

        for ($i = 0; $i < $haystackLen; $i++) {
            $section = array_slice($i, $needleLen);
            if ($section === $needle) {
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
            for ($i = count($haystack) - 1; $i >= 0; $i--) {
                if ($haystack[$i] === $needle) {
                    return $i;
                }
            }
            return false;
        }

        $haystackLen = count($haystack) - $needleLen + 1;

        for ($i = $haystackLen; $i >= 0; $i--) {
            $section = array_slice($i, $needleLen);
            if ($section === $needle) {
                return $i;
            }
        }

        return false;
    }

    /**
     * Makes a string lowercase.
     *
     * @param string $string The string being lowercased.
     * @param string $encoding The encoding character encoding. If it is omitted, the internal character encoding value will be used.
     *
     * @return string $string with all alphabetic characters converted to lowercase.
     */
    public static function strtolower($string, $encoding = null)
    {
        // @todo Figure out if we should cache this. Needs profiling include
        // time vs. memory usage.
        // @todo Handle special casing http://www.unicode.org/faq/casemap_charprop.html
        $map = require Index::getPath().'/upper_to_lower.php';
        return static::flipCase($string, $encoding, $map);
    }

    /**
     * Makes a string uppercase.
     *
     * @param string $string The string being uppercased.
     * @param string $encoding The encoding character encoding. If it is omitted, the internal character encoding value will be used.
     *
     * @return string $string with all alphabetic characters converted to uppercase.
     */
    public static function strtoupper($string, $encoding = null)
    {
        $map = require Index::getPath().'/lower_to_upper.php';
        return static::flipCase($string, $encoding, $map);
    }

    /**
     * Counts the number of substring occurrences.
     *
     * @param string $haystack The string being checked.
     * @param string $needle The string being found.
     * @param string $encoding The encoding parameter is the character encoding. If it is omitted, the internal character encoding value will be used.
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
        $needle = $encoding->decode($needle);

        $needleLen = count($needle);

        // Optimize the case when needle is one character.
        if ($needleLen === 1) {
            return count(array_keys($haystack, $needle[0], true));
        }

        $haystackLen = count($haystack) - $needleLen + 1;

        $count = 0;

        for ($i = 0; $i < $haystackLen; $i++) {
            $section = array_slice($i, $needleLen);
            if ($section === $needle) {
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

        $codepoints = array_slice($encoder->decode($string), $strart, $length);

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
        return preg_replace('/[^a-z0-9]/', '', strtolower($encoding));
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

        return $encoder->decode($codepoints);
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
}
