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

use Liip\RMT\Context;

class SemanticGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getVersionValues
     */
    public function testIncrement($current, $type, $label, $result)
    {
        $options = array();
        if ($type){
            $options['type'] = $type;
        }

        if ($label) {
            $options['label'] = $label;
        }

        $generator = new \Liip\RMT\Version\Generator\SemanticGenerator();
        $this->assertEquals($result, $generator->generateNextVersion($current, $options));
    }

    public function getVersionValues()
    {
        return array(
            array('1.0.0',  'patch', 'none', '1.0.1'),
            array('1.23.0', 'minor', 'none', '1.24.0'),
            array('1.1.19', 'minor', 'none', '1.2.0'),
            array('1.0.0',  'major', 'none', '2.0.0'),
            array('1.19.3', 'major', 'none', '2.0.0'),
            array('3.3.3',  'major', 'none', '4.0.0'),
            array('3.3.3',  'major', 'alpha', '4.0.0-alpha'),
            array('4.0.0-aplha2',  'major', 'beta', '4.0.0-beta'),
            array('3.3.3',  'minor', 'beta', '3.4.0-beta'),
            array('4.0.0-beta',  'minor', 'beta', '4.0.0-beta2'),
            array('4.0.0',  'minor', 'rc', '4.1.0-rc'),
            array('4.0.0-rc',  'minor', 'none', '4.0.0'),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The option [type] must be one of: {patch, minor, major}, "full" given
     */
    public function testIncrementWithInvalidType()
    {
        $generator = new \Liip\RMT\Version\Generator\SemanticGenerator();
        $generator->generateNextVersion('1.0.0', array('type'=>'full', 'label'=>'none'));
    }

    public function testCompare()
    {
        $generator = new \Liip\RMT\Version\Generator\SemanticGenerator();
        $this->assertEquals(-1, $generator->compareTwoVersions('1.0.0', '1.0.1'));
        $this->assertEquals(-1, $generator->compareTwoVersions('1.0.0-beta', '1.0.0'));
        $this->assertEquals(0, $generator->compareTwoVersions('1.0.0', '1.0.0'));
        $this->assertEquals(1, $generator->compareTwoVersions('1.0.1', '1.0.0'));
        $this->assertEquals(1, $generator->compareTwoVersions('1.0.11', '1.0.1'));
        $this->assertEquals(1, $generator->compareTwoVersions('1.0.1', '1.0.1-alpha'));
        $this->assertEquals(1, $generator->compareTwoVersions('1.0.1-beta', '1.0.1-alpha'));
        $this->assertEquals(1, $generator->compareTwoVersions('1.0.11-rc', '1.0.1-beta'));
        $this->assertEquals(1, $generator->compareTwoVersions('1.0.2', '1.0.1-rc'));
    }

}
