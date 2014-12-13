<?php

/**
 * @file
 * Contains \MbPhp\Tests\Gb18030.
 */

namespace MbPhp\Tests\Encoder;

use MbPhp\Encoder\Gb18030;

/**
 * @covers \MbPhp\Encoder\Gb18030
 */
class Gb18030Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider stringProvider
     */
    public function testEncode($string)
    {
        $encoder = new Gb18030();
        $codepoints = $encoder->decode($string);

        if (version_compare(phpversion(), '5.4', '>=')) {
          $this->assertSame(mb_strlen($string, 'gb18030'), count($codepoints));
          $this->assertSame(mb_convert_encoding($string, 'gb18030', 'gb18030'), $encoder->encode($codepoints));
        }
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalidEncode($string)
    {
        $encoder = new Gb18030();
        $codepoints = $encoder->decode($string);

        if (version_compare(phpversion(), '5.4', '>=')) {
          $this->assertSame(mb_strlen($string, 'gb18030'), count($codepoints));
        }
    }

    public function stringProvider()
    {
        $path = dirname(dirname(dirname(__FILE__))).'/test-resources/';
        $encodings = array(
            'utf-8',
            // 'euc-jp',
            // 'ibm866',
            // 'koi8-r',
            // 'ibm866',
            'gb18030',
        );

        $out = array();
        foreach ($encodings as $encoding) {
            $out[] = array(file_get_contents($path.$encoding.'.txt'));
        }

        return $out;
    }

    public function invalidProvider()
    {
        $path = dirname(dirname(dirname(__FILE__))).'/test-resources/';
        $encodings = array(
            'euc-jp',
            'ibm866',
            'koi8-r',
            'ibm866',
        );

        $out = array();
        foreach ($encodings as $encoding) {
            $out[] = array(file_get_contents($path.$encoding.'.txt'));
        }

        return $out;
    }
}
