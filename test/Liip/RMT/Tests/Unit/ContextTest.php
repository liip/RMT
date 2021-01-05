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

use InvalidArgumentException;
use Liip\RMT\Context;
use PHPUnit\Framework\TestCase;
use Liip\RMT\Tests\Unit\ServiceClass;

class ContextTest extends TestCase
{
    // SERVICE TESTS

    public function testSetAndGetService(): void
    {
        $context = Context::getInstance();
        $context->setService('foo', ServiceClass::class);
        $objectFoo = $context->getService('foo');
        self::assertInstanceOf(ServiceClass::class, $objectFoo);
        self::assertEquals($objectFoo, $context->getService('foo'), 'Two successive calls return the same object');
    }

    public function testSetAndGetServiceWithObject(): void
    {
        $context = Context::getInstance();
        $object = new ServiceClass();
        $context->setService('foo', $object);
        self::assertEquals($object, $context->getService('foo'));
    }

    public function testSetAndGetServiceWithOptions(): void
    {
        $context = Context::getInstance();
        $options = ['pi' => 3.14];
        $context->setService('foo', ServiceClass::class, $options);
        self::assertEquals($options, $context->getService('foo')->getOptions());
    }

    public function testGetServiceWithoutSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no service defined with id [abc]');

        Context::getInstance()->getService('abc');
    }

    public function testSetServiceWithInvalidClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The class [Bar] does not exist');

        Context::getInstance()->setService('foo', 'Bar');
    }

    public function testSetServiceWithInvalidObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('setService() only accept an object or a valid class name');

        $context = Context::getInstance();
        $context->setService('foo', 12);
    }

    // PARAM TESTS

    public function testSetAndGetParam(): void
    {
        $context = Context::getInstance();
        $context->setParameter('date', '11.11.11');
        self::assertEquals('11.11.11', $context->getParameter('date'));
    }

    public function testGetParamWithoutSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no param defined with id [abc]');

        $context = Context::getInstance();
        $context->getParameter('abc');
    }

    // LIST TESTS

    public function testAddToList(): void
    {
        $context = Context::getInstance();
        $context->addToList('prerequisites', ServiceClass::class);
        $context->addToList('prerequisites', Context::class);
        $objects = $context->getList('prerequisites');
        self::assertCount(2, $objects);
        self::assertInstanceOf(ServiceClass::class, $objects[0]);
        self::assertInstanceOf(Context::class, $objects[1]);
    }

    public function testAddToListWithOptions(): void
    {
        $context = Context::getInstance();
        $options = array('pi' => 3.14);
        $context->addToList('foo', ServiceClass::class, $options);
        $objects = $context->getList('foo');
        self::assertEquals($options, $objects[0]->getOptions());
    }

    public function testGetListParamWithoutAdd(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no list defined with id [abc]');

        $context = Context::getInstance();
        $context->getList('abc');
    }

    public function testAddToListWithInvalidClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The class [Bar] does not exist');

        $context = Context::getInstance();
        $context->addToList('foo', 'Bar');
    }

    public function testEmptyList(): void
    {
        $context = Context::getInstance();
        $context->createEmptyList('prerequisites');
        self::assertEquals(array(), $context->getList('prerequisites'));
    }
}
