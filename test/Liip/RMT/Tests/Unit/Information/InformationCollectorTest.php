<?php

namespace Liip\RMT\Tests\Unit\Information;

use Liip\RMT\Information\InformationCollector;
use Liip\RMT\Information\InformationRequest;

class InformationCollectorTest extends \PHPUnit_Framework_TestCase
{

    public function testRegisterRequest()
    {
        $ic = new InformationCollector();
        $this->assertFalse($ic->hasRequest('foo'));
        $ic->registerRequest(new InformationRequest('foo'));
        $this->assertTrue($ic->hasRequest('foo'));
    }

    public function testRegisterRequests()
    {
        $ic = new InformationCollector();
        $this->assertFalse($ic->hasRequest('foo')||$ic->hasRequest('type'));
        $ic->registerRequests(array(new InformationRequest('foo'), 'type'));
        $this->assertTrue($ic->hasRequest('foo'));
        $this->assertTrue($ic->hasRequest('type'));
    }


    public function testHasMissingInformation()
    {
        $ic = new InformationCollector();
        $ic->registerRequest(new InformationRequest('foo'));
        $this->assertTrue($ic->hasMissingInformation());
        $ic->setValueFor('foo', 'bar');
        $this->assertFalse($ic->hasMissingInformation());
    }

    public function testSetAndGetValueFor()
    {
        $ic = new InformationCollector();
        $ic->registerRequest(new InformationRequest('foo'));
        $ic->setValueFor('foo', 'bar');
        $this->assertEquals('bar', $ic->getValueFor('foo'));
    }

    public function testGetValueForWithDefault()
    {
        $ic = new InformationCollector();
        $this->assertEquals('bar', $ic->getValueFor('foo', 'bar'));
    }
}
