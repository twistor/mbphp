<?php

/**
 * @file
 * Contains \MbPhp\Tests\MbPhp.
 */

namespace MbPhp\Tests;

use MbPhp\Mb;

/**
 * @covers \MbPhp\Mb
 */
class MbTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckEncoding()
    {
        $this->assertTrue(Mb::checkEncoding('敏捷的棕色狐狸跳過了懶狗', 'utf-8'));
    }

    public function testConvertEncoding()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/ibm866.txt');
        // PHP 5.3 doesn't support ibm866 alias.
        $actual = mb_convert_encoding($string, 'utf-8', 'cp866');

        $converted = Mb::convertEncoding($string, 'utf-8', 'ibm866');
        $this->assertSame($actual, $converted);

        $this->assertSame($string, Mb::convertEncoding($converted, 'ibm866'));
    }

    public function testInternalEncoding()
    {
        // Test default.
        $this->assertSame('utf8', Mb::internalEncoding());

        $this->assertTrue(Mb::internalEncoding('ascii'));
        $this->assertSame('windows1252', Mb::internalEncoding());

        $this->assertFalse(Mb::internalEncoding('asdfasdf'));
        $this->assertTrue(Mb::internalEncoding('utf-8'));
    }

    public function testStrlen()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');
        $this->assertSame(mb_strlen($string, 'utf-8'), Mb::strlen($string));
    }

    public function testStrpos()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');
        $this->assertSame(mb_strpos($string, '狗', 0, 'utf-8'), Mb::strpos($string, '狗'));

        foreach (range(0, 10) as $offset) {
            $this->assertSame(mb_strpos($string, 'のろ', $offset, 'utf-8'), Mb::strpos($string, 'のろ', $offset));
        }
        $this->assertFalse(Mb::strpos($string, '!!', 5));
    }

    public function testStrrpos()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');
        $this->assertSame(mb_strrpos($string, '狗', 0, 'utf-8'), Mb::strrpos($string, '狗'));

        foreach (range(0, 10) as $offset) {
            $this->assertSame(mb_strrpos($string, 'のろ', $offset, 'utf-8'), Mb::strrpos($string, 'のろ', $offset));
        }
        $this->assertSame(mb_strrpos($string, 'のろ', -1, 'utf-8'), Mb::strrpos($string, 'のろ', -1));

        $this->assertFalse(Mb::strrpos($string, '!!'));
        $this->assertFalse(Mb::strrpos($string, '!'));
    }

    public function testStrtolower()
    {
        $this->assertSame('abcd', Mb::strtolower('ABCD'));
    }

    public function testStrtoupper()
    {
        $this->assertSame('ABCD', Mb::strtoupper('abcd'));
    }

    public function testSubstrCount()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');

        $this->assertSame(mb_substr_count($string, '狗'), Mb::substrCount($string, '狗'));
        $this->assertSame(mb_substr_count($string, 'のろ'), Mb::substrCount($string, 'のろ'));
    }

    public function testSubstr()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');

        $this->assertSame(mb_substr($string, 5), Mb::substr($string, 5));
    }

    public function testRegisterEncoder()
    {
        Mb::registerEncoder('asfasf', 'MbPhp\Encoder\Noop');
    }

    public function testMbTing()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/utf-8.txt');

        // Test invalid source encoding.
        $mb = mb_convert_encoding($string, 'utf-8', 'iso-8859-2');
        $mbPhp = Mb::convertEncoding($string, 'utf-8', 'iso-8859-2');
        $this->assertSame($mb, $mbPhp);

        // Test limited destination encoding.
        $mb = mb_convert_encoding($string, 'iso-8859-2', 'utf-8');
        $mbPhp = Mb::convertEncoding($string, 'iso-8859-2', 'utf-8');
        $this->assertSame($mb, $mbPhp);

    }
}
