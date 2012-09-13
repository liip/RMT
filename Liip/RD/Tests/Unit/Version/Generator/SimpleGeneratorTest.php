<?php

namespace Liip\RD\Tests\Unit\Version;

use Liip\RD\Context;

class SimpleGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIncrement()
    {
        $generator = new \Liip\RD\Version\Generator\SimpleGenerator(new Context());
        $this->assertEquals(4, $generator->generateNextVersion(3));
    }
}
