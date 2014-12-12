<?php

/**
 * @file
 * Contains MbPhp\Index.
 */

namespace MbPhp;

/**
 * Reads index arrays.
 */
class Index
{
    /**
     * Returns the index array for a given encoding.
     *
     * @param string $encoding The normalized name of the encoding.
     *
     * @return array An encoding index.
     */
    public static function get($encoding)
    {
        return require static::getIndexDir().'/index-'.$encoding.'.txt';
    }

    public static function getDir()
    {
        return dirname(dirname(__FILE__)).'/indexes';
    }
}
