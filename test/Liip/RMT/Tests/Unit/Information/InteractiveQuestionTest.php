<?php

namespace Liip\RMT\Tests\Unit\Information;

use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Information\InteractiveQuestion;

class InteractiveQuestionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefault()
    {
        $iq = new InteractiveQuestion(new InformationRequest('foo'));
        $this->assertFalse($iq->hasDefault());

        $iq = new InteractiveQuestion(new InformationRequest('foo', array('default'=>'bar')));
        $this->assertEquals('bar', $iq->getDefault());

        $iq = new InteractiveQuestion(new InformationRequest('fruit', array(
            'type'=>'choice',
            'choices' => array('apple', 'banana', 'cherry'),
            'choices_shortcuts' => array('a' => 'apple', 'b'=>'banana', 'c'=>'cherry'),
            'default' => 'banana'
        )));
        $this->assertEquals('b', $iq->getDefault());
    }

    public function testValidateChoicesWithShortcuts()
    {
        $ir = new InformationRequest('fruit', array(
            'type'=>'choice',
            'choices' => array('apple', 'banana', 'cherry'),
            'choices_shortcuts' => array('a' => 'apple', 'b'=>'banana', 'c'=>'cherry')
        ));

        $iq = new InteractiveQuestion($ir);
        $this->assertEquals('apple', $iq->validate('a'));
    }
}

