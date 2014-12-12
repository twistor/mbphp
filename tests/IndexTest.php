<?php

/**
 * @file
 * Contains \MbPhp\Tests\IndexTest.
 */

namespace MbPhp\Tests;

use MbPhp\Index;

/**
 * @covers \MbPhp\Index
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $this->assertSame(dirname(dirname(__FILE__)).'/indexes', Index::getDir());

        $index = require dirname(dirname(__FILE__)).'/indexes/index-ibm866.php';

        $this->assertSame($index, Index::get('ibm866'));
    }
}
