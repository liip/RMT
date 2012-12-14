<?php

namespace Liip\RMT\Tests\Unit\Version;

use Liip\RMT\Context;

class SimpleGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIncrement()
    {
        $generator = new \Liip\RMT\Version\Generator\SimpleGenerator();
        $this->assertEquals(4, $generator->generateNextVersion(3));
    }
}
