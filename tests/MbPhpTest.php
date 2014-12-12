<?php

/**
 * @file
 * Contains \MbPhp\Tests\MbPhp.
 */

namespace MbPhp\Tests;

use MbPhp\MbPhp;

/**
 * @covers \MbPhp\MbPhp
 */
class MbPhpTest extends \PHPUnit_Framework_TestCase
{
    public function textCheckEncoding()
    {
        $this->assertTrue(MbPhp::checkEncoding('敏捷的棕色狐狸跳過了懶狗', 'utf-8'));
    }

    public function testConvertEncoding()
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).'/test-resources/gb18030.txt');
        $actual = mb_convert_encoding($string, 'utf-8', 'gb18030');

        $this->assertSame($actual, MbPhp::convertEncoding($string, 'utf-8', 'gb18030'));
    }

    public function testStrtolower()
    {
        $this->assertSame('abcd', MbPhp::strtolower('ABCD'));
    }

    public function testStrtoupper()
    {
        $this->assertSame('ABCD', MbPhp::strtoupper('abcd'));
    }
}
