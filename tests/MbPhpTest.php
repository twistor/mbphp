<?php

/**
 * @file
 * Contains \MbPhp\Tests\MbPhp.
 */

namespace MbPhp\Tests;

use MbPhp\MbPhp;

/**
 */
class MbPhpTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckEncoding()
    {
        $this->assertTrue(MbPhp::checkEncoding('敏捷的棕色狐狸跳過了懶狗', 'utf-8'));
    }

    public function testConvertEncoding()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/ibm866.txt');
        // PHP 5.3 doesn't support ibm866 alias.
        $actual = mb_convert_encoding($string, 'utf-8', 'cp866');

        $converted = MbPhp::convertEncoding($string, 'utf-8', 'ibm866');
        $this->assertSame($actual, $converted);

        $this->assertSame($string, MbPhp::convertEncoding($converted, 'ibm866'));
    }

    public function testInternalEncoding()
    {
        // Test default.
        $this->assertSame('utf8', MbPhp::internalEncoding());

        $this->assertTrue(MbPhp::internalEncoding('ascii'));
        $this->assertSame('ascii', MbPhp::internalEncoding());

        $this->assertFalse(MbPhp::internalEncoding('asdfasdf'));
    }

    public function testStrlen()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');
        $this->assertSame(mb_strlen($string, 'utf-8'), MbPhp::strlen($string));
    }

    public function testStrpos()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');
        $this->assertSame(mb_strpos($string, '狗', 0, 'utf-8'), MbPhp::strpos($string, '狗'));

        $this->assertSame(mb_strpos($string, 'のろ', 0, 'utf-8'), MbPhp::strpos($string, 'のろ'));
        $this->assertSame(mb_strpos($string, 'のろ', 5, 'utf-8'), MbPhp::strpos($string, 'のろ', 5));


        $this->assertFalse(MbPhp::strpos($string, '!!', 5));
    }

    public function testStrrpos()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');
        $this->assertSame(mb_strrpos($string, '狗', 0, 'utf-8'), MbPhp::strrpos($string, '狗'));

        $this->assertSame(mb_strrpos($string, 'のろ', 0, 'utf-8'), MbPhp::strrpos($string, 'のろ'));
        $this->assertSame(mb_strrpos($string, 'のろ', 5, 'utf-8'), MbPhp::strrpos($string, 'のろ', 5));


        $this->assertFalse(MbPhp::strrpos($string, '!!'));
        $this->assertFalse(MbPhp::strrpos($string, '!'));
    }

    public function testStrtolower()
    {
        $this->assertSame('abcd', MbPhp::strtolower('ABCD'));
    }

    public function testStrtoupper()
    {
        $this->assertSame('ABCD', MbPhp::strtoupper('abcd'));
    }

    public function testSubstrCount()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');

        $this->assertSame(mb_substr_count($string, '狗'), MbPhp::substrCount($string, '狗'));
        $this->assertSame(mb_substr_count($string, 'のろ'), MbPhp::substrCount($string, 'のろ'));
    }

    public function testSubstr()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');

        $this->assertSame(mb_substr($string, '5'), MbPhp::substr($string, 5));
    }

    public function testRegisterEncoder()
    {
        MbPhp::registerEncoder('asfasf', 'MbPhp\Encoder\Noop');
    }
}
