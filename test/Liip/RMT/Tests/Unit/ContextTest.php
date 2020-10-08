<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Unit;

use Liip\RMT\Context;

class ContextTest extends \PHPUnit\Framework\TestCase
{
    // SERVICE TESTS

    public function testSetAndGetService()
    {
        $context = Context::getInstance();
        $context->setService('foo', '\Liip\RMT\Tests\Unit\ServiceClass');
        $objectFoo = $context->getService('foo');
        $this->assertInstanceOf('\Liip\RMT\Tests\Unit\ServiceClass', $objectFoo);
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
        $options = array('pi' => 3.14);
        $context->setService('foo', '\Liip\RMT\Tests\Unit\ServiceClass', $options);
        $this->assertEquals($options, $context->getService('foo')->getOptions());
    }

    public function testGetServiceWithoutSet()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('There is no service defined with id [abc]');
        Context::getInstance()->getService('abc');
    }

    public function testSetServiceWithInvalidClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The class [Bar] does not exist');
        Context::getInstance()->setService('foo', 'Bar');
    }

    public function testSetServiceWithInvalidObject()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('setService() only accept an object or a valid class name');
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

    public function testGetParamWithoutSet()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('There is no param defined with id [abc]');
        $context = Context::getInstance();
        $context->getParameter('abc');
    }

    // LIST TESTS

    public function testAddToList()
    {
        $context = Context::getInstance();
        $context->addToList('prerequisites', '\Liip\RMT\Tests\Unit\ServiceClass');
        $context->addToList('prerequisites', '\Liip\RMT\Context');
        $objects = $context->getList('prerequisites');
        $this->assertCount(2, $objects);
        $this->assertInstanceOf('\Liip\RMT\Tests\Unit\ServiceClass', $objects[0]);
        $this->assertInstanceOf('\Liip\RMT\Context', $objects[1]);
    }

    public function testAddToListWithOptions()
    {
        $context = Context::getInstance();
        $options = array('pi' => 3.14);
        $context->addToList('foo', '\Liip\RMT\Tests\Unit\ServiceClass', $options);
        $objects = $context->getList('foo');
        $this->assertEquals($options, $objects[0]->getOptions());
    }

    public function testGetListParamWithoutAdd()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('There is no list defined with id [abc]');
        $context = Context::getInstance();
        $context->getList('abc');
    }

    public function testAddToListWithInvalidClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The class [Bar] does not exist');
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
