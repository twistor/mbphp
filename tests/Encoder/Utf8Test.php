<?php

/**
 * @file
 * Contains \MbPhp\Tests\Utf8.
 */

namespace MbPhp\Tests\Encoder;

use MbPhp\Encoder\Utf8;

/**
 */
class Utf8Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider stringProvider
     */
    public function testEncode($string)
    {
        $encoder = new Utf8();
        $codepoints = $encoder->decode($string);

        $this->assertSame(mb_strlen($string, 'utf-8'), count($codepoints));
        $this->assertSame(mb_convert_encoding($string, 'utf-8', 'utf-8'), $encoder->encode($codepoints));
    }

    public function stringProvider()
    {
        $path = dirname(dirname(dirname(__FILE__))).'/test-resources/';
        $encodings = array(
            'utf-8',
            // 'euc-jp',
            'ibm866',
            // 'koi8-r',
            'cp866',
            // 'gb18030',
        );

        $out = array();
        foreach ($encodings as $encoding) {
            $out[] = array(file_get_contents($path.$encoding.'.txt'));
        }

        return $out;
    }
}
