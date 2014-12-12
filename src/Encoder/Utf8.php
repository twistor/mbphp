<?php

/**
 * @file
 * Contains \MbPhp\Encoder\Utf8.
 */

namespace MbPhp\Encoder;

/**
 * Encoder for utf-8.
 */
class Utf8 implements Encoder
{
    /**
     * {@inheritdoc}
     */
    public function decode($string)
    {
        $len = strlen($string);
        $output = array();
        $seen = $needed = 0;
        $lower = 0x80;
        $upper = 0xBF;

        for ($i = 0; $i < $len; $i++) {
            $byte = ord($string[$i]);

            if ($needed === 0) {
                if ($byte >= 0x00 && $byte <= 0x7F) {
                    $output[] = $byte;
                    continue;
                } elseif ($byte >= 0xC2 && $byte <= 0xDF) {
                    $needed = 1;
                    $codepoint = $byte - 0xC0;
                } elseif ($byte >= 0xE0 && $byte <= 0xEF) {
                    if ($byte === 0xE0) {
                        $lower = 0xA0;
                    } elseif ($byte === 0xED) {
                        $upper = 0x9F;
                    }
                    $needed = 2;
                    $codepoint = $byte - 0xE0;
                } elseif ($byte >= 0xF0 && $byte <= 0xF4) {
                    if ($byte === 0xF0) {
                        $lower = 0x90;
                    } elseif ($byte === 0xF4) {
                        $upper = 0x8F;
                    }
                    $needed = 3;
                    $codepoint = $byte - 0xF0;
                } else {
                    // Returns ord('?');
                    $output[] = 63;
                    $codepoint = $needed = $seen = 0;
                    continue;
                }
                $codepoint = $codepoint <<  (6 * $needed);
                continue;
            }

            if ($byte < $lower || $byte > $upper) {
                // Returns ord('?');
                $output[] = 63;
                $codepoint = $needed = $seen = 0;
                continue;
            }

            $lower = 0x80;
            $upper = 0xBF;
            $seen++;
            $codepoint = $codepoint + (($byte - 0x80) << (6 * ($needed - $seen)));

            if ($seen !== $needed) {
                continue;
            }
            $output[] = $codepoint;
            $codepoint = $needed = $seen = 0;
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $codepoints)
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
                $count = 3;
                $offset = 0xF0;
                $codepoint = 0xFFFD;
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
