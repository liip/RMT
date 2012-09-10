<?php

namespace Liip\RD\Tests\Unit;

use Liip\RD\Context;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    public function testSetAndGetService()
    {
        $context = new Context();
        $context->setService('date', '\DateTime');
        $dateTime = $context->getService('date');
        $this->assertInstanceOf('\DateTime', $dateTime);
        $this->assertEquals($dateTime, $context->getService('date'), 'Two successive calls return the same object');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedMessage There is no service define with id [date]
     */
    public function testGetServiceWithoutSet()
    {
        $context = new Context();
        $context->getService('date');
    }

    public function testSetAndGetParam()
    {
        $context = new Context();
        $context->setParam('date', '11.11.11');
        $this->assertEquals('11.11.11', $context->getParam('date'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedMessage There is no param define with id [date]
     */
    public function testGetParamWithoutSet()
    {
        $context = new Context();
        $context->getParam('date');
    }

    public function testAddToList()
    {
        $context = new Context();
        $context->addToList('prerequisites', '\DateTime');
        $context->addToList('prerequisites', '\Liip\RD\Context');
        $objects = $context->getList('prerequisites');
        $this->assertCount(2, $objects);
        $this->assertInstanceOf('\DateTime', $objects[0]);
        $this->assertInstanceOf('\Liip\RD\Context', $objects[1]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedMessage There is no list define with id [date]
     */
    public function testGetListParamWithoutAdd()
    {
        $context = new Context();
        $context->getList('date');
    }

    public function testEmptyList()
    {
        $context = new Context();
        $context->createEmptyList('prerequisites');
        $this->assertEquals(array(), $context->getList('prerequisites'));
    }
}
