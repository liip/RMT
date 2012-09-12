<?php

namespace Liip\RD\Tests\Unit;

use Liip\RD\Context;

class ContextTest extends \PHPUnit_Framework_TestCase
{

    // SERVICE TESTS

    public function testSetAndGetService()
    {
        $context = new Context();
        $context->setService('date', '\DateTime');
        $dateTime = $context->getService('date');
        $this->assertInstanceOf('\DateTime', $dateTime);
        $this->assertEquals($dateTime, $context->getService('date'), 'Two successive calls return the same object');
    }

    public function testSetAndGetServiceWithOptions()
    {
        $context = new Context();
        $options = array('pi'=>3.14);
        $context->setService('foo', '\Liip\RD\Tests\Unit\ClassWithOptions', $options);
        $object = $context->getService('foo');
        $this->assertEquals($options, $object->getOptions());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage There is no service define with id [date]
     */
    public function testGetServiceWithoutSet()
    {
        $context = new Context();
        $context->getService('date');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The class [Bar] does not exist
     */
    public function testSetServiceWithInvalidClass()
    {
        $context = new Context();
        $context->setService('foo', 'Bar');
    }


    // PARAM TESTS

    public function testSetAndGetParam()
    {
        $context = new Context();
        $context->setParam('date', '11.11.11');
        $this->assertEquals('11.11.11', $context->getParam('date'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage There is no param define with id [date]
     */
    public function testGetParamWithoutSet()
    {
        $context = new Context();
        $context->getParam('date');
    }


    // LIST TESTS

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

    public function testAddToListWithOptions()
    {
        $context = new Context();
        $options = array('pi'=>3.14);
        $context->addToList('foo', '\Liip\RD\Tests\Unit\ClassWithOptions', $options);
        $objects = $context->getList('foo');
        $this->assertEquals($options, $objects[0]->getOptions());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage There is no list define with id [date]
     */
    public function testGetListParamWithoutAdd()
    {
        $context = new Context();
        $context->getList('date');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The class [Bar] does not exist
     */
    public function testAddToListWithInvalidClass()
    {
        $context = new Context();
        $context->addToList('foo', 'Bar');
    }


    public function testEmptyList()
    {
        $context = new Context();
        $context->createEmptyList('prerequisites');
        $this->assertEquals(array(), $context->getList('prerequisites'));
    }
}
