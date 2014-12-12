<?php

/**
 * @file
 * Contains \MbPhp\Encoder\SingleByte.
 */

namespace MbPhp\Encoder;

use MbPhp\Index;

/**
 * Encodes single byte encodings.
 */
class SingleByte implements Encoder
{
    /**
     * The encoding.
     *
     * @var string
     */
    protected $encoding;

    /**
     * Constructs a SingleByte object.
     *
     * @param string $encoding The encoding.
     */
    public function __construct($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($string)
    {
        $index = Index::get($this->encoding);
        $len = strlen($string);

        $output = array();

        for ($i = 0; $i < $len; $i++) {
            $value = ord($string[$i]);

            if ($value < 0x80) {
                $output[$i] = $value;
            } else {
                $value = $value ^ 0x80;
                if (isset($index[$value])) {
                    $output[$i] = $index[$value];
                } else {
                    throw new \InvalidArgumentException();
                }
            }
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($codepoints)
    {
        $index = array_flip(Index::get($this->encoding));

        $output = '';
        foreach ($codepoints as $token) {
            if ($token < 0x80) {
                $output .= chr($token);
            } elseif (isset($index[$token])) {
                $output .= chr(0x80 | $index[$token]);
            } else {
                throw new \InvalidArgumentException();
            }
        }

        return $output;
    }
}
