<?php

namespace Liip\RD\Tests\Unit\Version;

use Liip\RD\Context;

class SemanticGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getVersionValues
     */
    public function testIncrement($current, $type, $result)
    {
        $options = array();
        if ($type){
            $options['type'] = $type;
        }
        $generator = new \Liip\RD\Version\Generator\SemanticGenerator(new Context());
        $this->assertEquals($result, $generator->generateNextVersion($current, $options));
    }

    public function getVersionValues()
    {
        return array(
            array('1.0.0',  'patch', '1.0.1'),
            array('1.23.0', 'minor', '1.24.0'),
            array('1.1.19', 'minor', '1.2.0'),
            array('1.0.0',  'major',  '2.0.0'),
            array('1.19.3', 'major',  '2.0.0'),
            array('3.3.3',  'major',  '4.0.0')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The option [type] must be one of: {patch, minor, major}, "full" given
     */
    public function testIncrementWithInvalidType()
    {
        $generator = new \Liip\RD\Version\Generator\SemanticGenerator(new Context());
        $generator->generateNextVersion('1.0.0', array('type'=>'full'));
    }


}
