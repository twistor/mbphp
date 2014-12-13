<?php

/**
 * @file
 * Contains \MbPhp\Tests\Utf8.
 */

namespace MbPhp\Tests\Encoder;

use MbPhp\Encoder\Utf8;

/**
 * @covers \MbPhp\Encoder\Utf8
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

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalidEncode($string)
    {
        $encoder = new Utf8();
        $codepoints = $encoder->decode($string);

        $this->assertSame(mb_strlen($string, 'utf-8'), count($codepoints));
    }

    public function stringProvider()
    {
        $path = dirname(dirname(dirname(__FILE__))).'/test-resources/';
        $encodings = array(
            'utf-8',
            // 'euc-jp',
            'ibm866',
            // 'koi8-r',
            // 'gb18030',
        );

        $out = array();
        foreach ($encodings as $encoding) {
            $out[] = array(file_get_contents($path.$encoding.'.txt'));
        }

        if (version_compare(phpversion(), '5.4', '>=')) {
          $out[] = array(file_get_contents($path.'ibm866.txt'));
        }

        return $out;
    }

    public function invalidProvider()
    {
        $path = dirname(dirname(dirname(__FILE__))).'/test-resources/';
        $encodings = array(
            // 'euc-jp',
            'koi8-r',
            // 'gb18030',
        );

        $out = array();
        foreach ($encodings as $encoding) {
            $out[] = array(file_get_contents($path.$encoding.'.txt'));
        }

        if (version_compare(phpversion(), '5.4', '>=')) {
          $out[] = array(file_get_contents($path.'ibm866.txt'), 'ibm866');
        }

        return $out;
    }
}
