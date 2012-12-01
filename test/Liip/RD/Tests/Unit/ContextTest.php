<?php

namespace Liip\RD\Tests\Unit;

use Liip\RD\Context;

use Liip\RD\Tests\Unit\ServiceClass;

class ContextTest extends \PHPUnit_Framework_TestCase
{

    // SERVICE TESTS

    public function testSetAndGetService()
    {
        $context = Context::getInstance();
        $context->setService('foo', '\Liip\RD\Tests\Unit\ServiceClass');
        $objectFoo = $context->getService('foo');
        $this->assertInstanceOf('\Liip\RD\Tests\Unit\ServiceClass', $objectFoo);
        $this->assertEquals($objectFoo, $context->getService('foo'), 'Two successive calls return the same object');
    }

    public function testSetAndGetServiceWithObject()
    {
        $context = Context::getInstance();
        $object = new ServiceClass();
        $context->setService('foo', $object);
        $this->assertEquals($object, $context->getService('foo'));
    }

    public function testSetAndGetServiceWithOptions()
    {
        $context = Context::getInstance();
        $options = array('pi'=>3.14);
        $context->setService('foo', '\Liip\RD\Tests\Unit\ServiceClass', $options);
        $this->assertEquals($options, $context->getService('foo')->getOptions());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage There is no service define with id [abc]
     */
    public function testGetServiceWithoutSet()
    {
        Context::getInstance()->getService('abc');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The class [Bar] does not exist
     */
    public function testSetServiceWithInvalidClass()
    {
        Context::getInstance()->setService('foo', 'Bar');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage setService() only accept an object or a valid class name
     */
    public function testSetServiceWithInvalidObject()
    {
        $context = Context::getInstance();
        $context->setService('foo', 12);
    }



    // PARAM TESTS

    public function testSetAndGetParam()
    {
        $context = Context::getInstance();
        $context->setParameter('date', '11.11.11');
        $this->assertEquals('11.11.11', $context->getParameter('date'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage There is no param define with id [abc]
     */
    public function testGetParamWithoutSet()
    {
        $context = Context::getInstance();
        $context->getParameter('abc');
    }


    // LIST TESTS

    public function testAddToList()
    {
        $context = Context::getInstance();
        $context->addToList('prerequisites', '\Liip\RD\Tests\Unit\ServiceClass');
        $context->addToList('prerequisites', '\Liip\RD\Context');
        $objects = $context->getList('prerequisites');
        $this->assertCount(2, $objects);
        $this->assertInstanceOf('\Liip\RD\Tests\Unit\ServiceClass', $objects[0]);
        $this->assertInstanceOf('\Liip\RD\Context', $objects[1]);
    }

    public function testAddToListWithOptions()
    {
        $context = Context::getInstance();
        $options = array('pi'=>3.14);
        $context->addToList('foo', '\Liip\RD\Tests\Unit\ServiceClass', $options);
        $objects = $context->getList('foo');
        $this->assertEquals($options, $objects[0]->getOptions());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage There is no list define with id [abc]
     */
    public function testGetListParamWithoutAdd()
    {
        $context = Context::getInstance();
        $context->getList('abc');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The class [Bar] does not exist
     */
    public function testAddToListWithInvalidClass()
    {
        $context = Context::getInstance();
        $context->addToList('foo', 'Bar');
    }


    public function testEmptyList()
    {
        $context = Context::getInstance();
        $context->createEmptyList('prerequisites');
        $this->assertEquals(array(), $context->getList('prerequisites'));
    }
}
