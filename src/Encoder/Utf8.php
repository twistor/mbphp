<?php

/**
 * @file
 * Contains \MbPhp\Encoder\Utf8.
 */

namespace MbPhp\Encoder;

/**
 * Encoder for utf-8.
 */
class Utf8
{
    /**
     * {@inheritdoc}
     */
    public function decode($string, $encoding)
    {
        $len = strlen($string);
        $output = array();

        for ($i = 0; $i < $len; $i++) {
            $byte = ord($string[$i]) & 0xFF;

            // Zero continuation (0 to 127).
            if (($byte & 0x80) === 0) {
                $output[] = $byte;
            }

            // One continuation (128 to 2047).
            elseif (($byte & 0xE0) === 0xC0) {
                $next = $this->getChar($string, ++$i);

                $return = (($byte & 0x1F) << 6) | $next;
                if ($return >= 128) {
                    $output[] = $return;
                } else {
                    throw new \InvalidArgumentException();
                }
            }

            // Two continuation (2048 to 55295 and 57344 to 65535).
            elseif (($byte & 0xF0) === 0xE0) {
                $next1 = $this->getChar($string, ++$i);
                $next2 = $this->getChar($string, ++$i);

                $return = (($byte & 0x0F) << 12) | ($next1 << 6) | $next2;

                if (($return > 2047 && $return < 55296) || ($return > 57343 && $return < 65536)) {
                    $output[] = $return;
                } else {
                    throw new \InvalidArgumentException();
                }
            }

            // Three continuation (65536 to 1114111).
            elseif (($byte & 0xF8) === 0xF0) {
                $next1 = $this->getChar($string, ++$i);
                $next2 = $this->getChar($string, ++$i);
                $next3 = $this->getChar($string, ++$i);

                $return = (($byte & 0x0F) << 18) | ($next1 << 12) | ($next2 << 6) | $next3;
                if ($return >= 65536 && $return <= 1114111) {
                    $output[] = $return;
                } else {
                    throw new \InvalidArgumentException();
                }
            }
        }

        return $output;
    }

    /**
     * Returns the character at a given index.
     *
     * @param string $string The string being decoded.
     * @param int    $index  The position in the string.
     *
     * @return int The byte character at the given position.
     */
    protected function getChar($string, $index)
    {
        $char = isset($string[$index]) ? ord($string[$index]) & 0xFF : false;
        if ($char === false) {
            throw new \InvalidArgumentException();
        }

        if (($char & 0xC0) === 0x80) {
            return $char & 0x3F;
        }
        throw new \InvalidArgumentException();
    }

    /**
     * {@inheritdoc}
     */
    public function encode($codepoints, $encoding)
    {
        $output = '';
        foreach ($codepoints as $codepoint) {
            if ($codepoint < 0x80) {
                $output .= chr($codepoint);
                continue;
            }

            if ($codepoint < 0x800) {
                $count = 1;
                $offset = 0xC0;
            } elseif ($codepoint < 0x10000) {
                $count = 2;
                $offset = 0xE0;
            } elseif ($codepoint <= 0x10FFFF) {
                $count = 3;
                $offset = 0xF0;
            } else {
                throw new \InvalidArgumentException();
            }

            $output .= chr(($codepoint >> (6 * $count)) + $offset);

            while ($count) {
                $count--;
                $output .=  chr(0x80 | ($codepoint >> (6 * ($count)) & 0x3F));
            }
        }

        return $output;
    }
}
