<?php

/**
 * @file
 * Contains \MbPhp\Tests\SingleByte.
 */

namespace MbPhp\Tests\Encoder;

use MbPhp\Encoder\SingleByte;
use MbPhp\Mb;

/**
 * @covers \MbPhp\Encoder\SingleByte
 */
class SingleByteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider stringProvider
     */
    public function testEncode($string, $mbEncoding)
    {
        $encoding = Mb::normalize($mbEncoding);
        $encoder = new SingleByte($encoding);
        $codepoints = $encoder->decode($string);

        $this->assertSame(mb_strlen($string, $mbEncoding), count($codepoints));
        $this->assertSame(mb_convert_encoding($string, $mbEncoding, $mbEncoding), $encoder->encode($codepoints));
    }

    public function stringProvider()
    {
        $path = dirname(dirname(dirname(__FILE__))).'/test-resources/';
        $encodings = array(
            'ibm866' => 'ibm866',
            // 'ibm866' => 'ascii',
            'utf-8' => 'iso-8859-2',
            // 'utf-8' => 'ascii',
            'gb18030' => 'iso-8859-2',
            'gb18030' => 'ibm866',
            'euc-jp' => 'iso-8859-2',
            'euc-jp' => 'koi8r',
            // 'euc-jp' => 'ascii',
        );

        $out = array();
        foreach ($encodings as $encoding => $name) {
            $out[] = array(file_get_contents($path.$encoding.'.txt'), $name);
        }

        return $out;
    }
}
