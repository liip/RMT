<?php

namespace Liip\RMT\Tests\Unit\Information;

use Liip\RMT\Information\InformationRequest;

class InformationRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testSetAndGetValue()
    {
        $ir = new InformationRequest('foo', array('type'=>'text'));
        $this->assertFalse($ir->hasValue());
        $ir->setValue('bar');
        $this->assertTrue($ir->hasValue());
        $this->assertEquals('bar', $ir->getValue());
    }

    /**
     * @dataProvider getDataForValidationSuccess
     */
    public function testValidationSuccess($options, $value, $expected=null)
    {
        if (func_num_args()==2){
            $expected = $value;
        }
        $ir = new InformationRequest('foo', $options);
        $ir->setValue($value);
        $this->assertEquals($expected, $ir->getValue());
    }
    public function getDataForValidationSuccess()
    {
        return array(
            array(array('type'=>'text'),  'string'),
            array(array('type'=>'yes-no'), 'y'),
            array(array('type'=>'yes-no'), 'n'),
            array(array('type'=>'yes-no'), 'yes', 'y'),
            array(array('type'=>'yes-no'), 'no' , 'n'),
            array(array('type'=>'choice', 'choices' => array('apple', 'banana', 'cherry')), 'apple'),
            array(array('type'=>'confirmation'), true),
            array(array('type'=>'confirmation'), false)
        );
    }

    /**
     * @dataProvider getDataForValidationFail
     * @expectedException \InvalidArgumentException
     */
    public function testValidationFail($options, $value)
    {
        $ir = new InformationRequest('foo', $options);
        $ir->setValue($value);
    }
    public function getDataForValidationFail()
    {
        $choices = array('apple', 'banana', 'cherry');
        return array(
            array(array('type'=>'text'), true),
            array(array('type'=>'text'), ''),
            array(array('type'=>'yes-no'), 'foo'),
            array(array('type'=>'yes-no'), 19),
            array(array('type'=>'choice', 'choices' => $choices), 'mango')
        );
    }

}
