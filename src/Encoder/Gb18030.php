<?php

/**
 * @file
 * Contains \MbPhp\Encoder\Gb18030.
 */

namespace MbPhp\Encoder;

use MbPhp\Index;

/**
 * Encodes gb18030.
 */
class Gb18030 implements Encoder
{
    /**
     * {@inheritdoc}
     */
    public function decode($string)
    {
        // Load the range index.
        $ranges = require Index::getDir().'/index-gb18030ranges.php';

        // Load the normal index.
        $index = Index::get('gb18030');

        $first = $second = $third = 0x00;
        $output = array();

        $len = strlen($string);
        $output = array();

        for ($i = 0; $i < $len; $i++) {
            $byte = ord($string[$i]);

            if ($third !== 0x00) {
                $codepoint = null;
                if ($byte >= 0x30 && $byte <= 0x39) {
                    $pointer = ((($first - 0x81) * 10 + $second - 0x30) * 126 + $third - 0x81) * 10 + $byte - 0x30;

                    $output[] = $this->getRangesCodePoint($ranges, $pointer);
                    $first = $second = $third = 0x00;
                }
            } elseif ($second !== 0x00) {
                if ($byte >= 0x81 && $byte <= 0xFE) {
                    $third = $byte;
                } else {
                    throw new \InvalidArgumentException();
                }
            } elseif ($first !== 0x00) {
                if ($byte >= 0x30 && $byte <= 0x39) {
                    $second = $byte;
                    continue;
                }
                $lead = $first;
                $first = 0x00;
                $offset = $byte < 0x7F ? 0x40 : 0x41;

                if (($byte >= 0x40 && $byte <= 0x7E) || ($byte >= 0x80 && $byte <= 0xFE)) {
                    $pointer = ($lead - 0x81) * 190 + ($byte - $offset);
                } else {
                    throw new \InvalidArgumentException();
                }

                $output[] = $index[$pointer];
            } elseif ($byte >= 0x00 && $byte <= 0x7F) {
                $output[] = $byte;
            } elseif ($byte === 0x80) {
                $output[] = 8364;
            } elseif ($byte >= 0x81 && $byte <= 0xFE) {
                $first = $byte;
            } else {
                throw new \InvalidArgumentException();
            }
        }

        if ($first !== 0x00 || $second !== 0x00 || $third !== 0x00) {
            throw new \InvalidArgumentException();
        }

        return $output;
    }

    protected function getRangesCodePoint(array $ranges, $pointer)
    {
        if (($pointer > 39419 && $pointer < 189000) || $pointer > 1237575) {
            throw new \InvalidArgumentException();
        }

        $offset = $codepoint;

        while ($offset) {
            $pointerOffset = isset($ranges[$offset]) ? $ranges[$offset] : false;

            if ($pointerOffset !== false) {
                break;
            }
            $offset--;
        }

        return $pointerOffset + $codepoint - $offset;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $codepoints)
    {
        $ranges = require Index::getDir().'/index-gb18030ranges.php';
        $ranges = array_flip($ranges);
        $index = array_flip(Index::get('gb18030'));

        $output = '';
        $gbk = false;
        foreach ($codepoints as $codepoint) {
            if ($codepoint >= 0x00 && $codepoint <= 0x7F) {
                $output .= chr($codepoint);
                continue;
            }

            // Euro symbol U+20AC.
            if ($gbk && $codepoint === 8364) {
                $output .= chr(0x80);
                continue;
            }

            if (isset($index[$codepoint])) {
                $pointer = $index[$codepoint];

                $lead =  $pointer / 190 + 0x81;
                $trail = $pointer % 190;
                $offset = $trail < 0x3F ? 0x40 : 0x41;

                $output .= chr($lead).chr($trail + $offset);
                continue;
            }
            if ($gbk) {
                throw new \InvalidArgumentException();
            }

            $pointer = $this->getRangesPointer($ranges, $codepoint);

            $byte1 = $pointer / 10 / 126 / 10;
            $pointer = $pointer - $byte1 * 10 * 126 * 10;
            $byte2 = $pointer / 10 / 126;
            $pointer = $pointer - $byte2 * 10 * 126;
            $byte3 = $pointer / 10;
            $byte4 = $pointer - $byte3 * 10;

            $output .= chr($byte1 + 0x81).
                       chr($byte2 + 0x30).
                       chr($byte3 + 0x81).
                       chr($byte4 + 0x30);
        }

        return $output;
    }

    protected function getRangesPointer(array $ranges, $codepoint)
    {
        $offset = $codepoint;

        while ($offset) {
            $pointerOffset = isset($ranges[$offset]) ? $ranges[$offset] : false;

            if ($pointerOffset !== false) {
                break;
            }
            $offset--;
        }

        return $pointerOffset + $codepoint - $offset;
    }
}
