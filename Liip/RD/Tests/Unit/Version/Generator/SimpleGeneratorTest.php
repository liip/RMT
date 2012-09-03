<?php

namespace Liip\RD\Tests\Unit\Version;

class SimpleGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIncrement()
    {
        $generator = new \Liip\RD\Version\Generator\SimpleGenerator();
        $this->assertEquals(4, $generator->getNextVersion(3));
    }
}
