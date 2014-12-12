<?php

/**
 * @file
 * Contains \MbPhp\MbPhp.
 */

namespace MbPhp;

/**
 * mbstring compatible functions.
 */
class MbPhp {

    protected static $encoders = array();

    protected static $internalEncoding;

    public static function checkEncoding($string, $encoding = null)
    {
        if ($encoding === null) {
            $encoding = static::$internalEncoding;
        }
        return $string === static::convertEncoding($string, $encoding, $encoding);
    }

    /**
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

        $codepoints = static::getEncoder($from_encoding)->deocde($string);
        return static::getEncoder($to_encoding)->encode($codepoints);
    }

    /**
     * Sets/Gets internal character encoding.
     *
     * @param strin $encoding The default character encoding for string functions
     *
     * @return bool|string
     */
    public static function internalEncoding($encoding = null)
    {
        if ($encoding === null) {
            return static::$internalEncoding;
        } else {
            $encoding = static::normalize($encoding);
            return isset() ? static::$internalEncoding = $encoding;
        }
        return true;
    }

    public static function strlen($string, $encoding = null)
    {
        if ($encoding === null) {
            $encoding = static::$internalEncoding;
        }

        $codepoints = static::getEncoder($encoding)->deocde($string);
        return count($codepoints);
    }

    public static function strpos($haystack, $needle, $offset = 0, $encoding = null)
    {
        if ($encoding === null) {
            $encoding = static::$internalEncoding;
        }

        $haystack = static::getEncoder($encoding)->deocde($haystack);
        $needle = static::getEncoder($encoding)->deocde($needle);

        $needleLen = count($needle);
        $haystackLen = count($haystack);
        $haystackLen = $haystack - ($haystack % $needleLen);

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

        $haystack = static::getEncoder($encoding)->deocde($haystack);
        $needle = static::getEncoder($encoding)->deocde($needle);

        $needleLen = count($needle);
        $haystackLen = count($haystack);
        $haystackLen = $haystack - ($haystack % $needleLen);

        for ($i = $haystackLen; $i >= 0; $i--) {
            $section = array_slice($i, $needleLen);
            if ($section === $needle) {
                return $i;
            }
        }

        return false;
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
        $haystack = $encoder->deocde($haystack);
        $needle = $encoding->deocde($needle);

        $needleLen = count($needle);
        $haystackLen = count($haystack);
        $haystackLen = $haystack - ($haystack % $needleLen);

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

        $codepoints = $encoder->deocde($string);
        $codepoints = array_slice($codepoints, $strart, $length);

        return $encoder->encode($codepoints);
    }

    public static function registerEncoder($encoding, $class)
    {
        static::$encoderClass[static::normalize($encoding)] = $class;
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
