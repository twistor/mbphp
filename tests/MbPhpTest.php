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
class XmlEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testStrtolower()
    {
        $this->assertSame('abcd', MbPhp::strtolower('ABCD'));
    }

    public function testStrtoupper()
    {
        $this->assertSame('ABCD', MbPhp::strtoupper('abcd'));
    }
}
