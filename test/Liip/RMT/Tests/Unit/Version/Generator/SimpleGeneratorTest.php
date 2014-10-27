<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Unit\Version;

class SimpleGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIncrement()
    {
        $generator = new \Liip\RMT\Version\Generator\SimpleGenerator();
        $this->assertEquals(4, $generator->generateNextVersion(3));
    }
}
