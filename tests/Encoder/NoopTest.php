<?php

/**
 * @file
 * Contains \MbPhp\Tests\Noop.
 */

namespace MbPhp\Tests\Encoder;

use MbPhp\Encoder\Noop;

/**
 * @covers \MbPhp\Encoder\Noop
 */
class NoopTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $encoder = new Noop();
        $this->assertSame('', $encoder->encode(array(120)));
        $this->assertSame(array(), $encoder->decode('asdfs'));
    }
}
