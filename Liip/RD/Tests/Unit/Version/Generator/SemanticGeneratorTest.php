<?php

namespace Liip\RD\Tests\Unit\Version;

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
        $generator = new \Liip\RD\Version\Generator\SemanticGenerator();
        $this->assertEquals($result, $generator->generateNextVersion($current, $options));
    }

    public function getVersionValues()
    {
        return array(
            array('1.0.0',  'patch', '1.0.1'),
            array('1.0.23', null,    '1.0.24'),  // Increment without type give a patch level
            array('1.23.0', 'minor', '1.24.0'),
            array('1.1.19', 'minor', '1.2.0'),
            array('1.0.0',  'major',  '2.0.0'),
            array('1.19.3', 'major',  '2.0.0'),
            array('3.3.3',  'major',  '4.0.0')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedMessage \ The option "type" must one of [minor, major, full], "toto" given
     */
    public function testIncrementWithInvalidType()
    {
        $generator = new \Liip\RD\Version\Generator\SemanticGenerator();
        $generator->generateNextVersion('1.0.0', array('type'=>'full'));
    }


}
