<?php

/**
 * @file
 * Contains \MbPhp\Encoder\Encoder.
 */

namespace MbPhp\Encoder;

/**
 * Interface for encoders.
 */
interface Encoder
{
    /**
     * Decodes a string.
     *
     * @param string string An encoded string.
     *
     * @return array A list of codepoints.
     */
    public function decode($string);

    /**
     * Encodes a list of codepoints.
     *
     * @param array A list of codepoints.
     *
     * @return string An encoded string.
     */
    public function encode(array $codepoints);
}
