<?php

/**
 * @file
 * Contains \MbPhp\Encoder\Noop.
 */

namespace MbPhp\Encoder;

/**
 * An encoder that does nothing. Used for unsupported encodings.
 */
class Noop implements Encoder
{
    /**
     * {@inheritdoc}
     */
    public function decode($string)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function encode($codepoints)
    {
        return '';
    }
}
