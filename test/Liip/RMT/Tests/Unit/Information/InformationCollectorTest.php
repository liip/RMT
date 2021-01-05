<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Unit\Information;

use Liip\RMT\Information\InformationCollector;
use Liip\RMT\Information\InformationRequest;
use PHPUnit\Framework\TestCase;

class InformationCollectorTest extends TestCase
{
    public function testRegisterRequest(): void
    {
        $ic = new InformationCollector();
        self::assertFalse($ic->hasRequest('foo'));
        $ic->registerRequest(new InformationRequest('foo'));
        self::assertTrue($ic->hasRequest('foo'));
    }

    public function testRegisterRequests(): void
    {
        $ic = new InformationCollector();
        self::assertFalse($ic->hasRequest('foo') || $ic->hasRequest('type'));
        $ic->registerRequests(array(new InformationRequest('foo'), 'type'));
        self::assertTrue($ic->hasRequest('foo'));
        self::assertTrue($ic->hasRequest('type'));
    }

    public function testHasMissingInformation(): void
    {
        $ic = new InformationCollector();
        $ic->registerRequest(new InformationRequest('foo'));
        self::assertTrue($ic->hasMissingInformation());
        $ic->setValueFor('foo', 'bar');
        self::assertFalse($ic->hasMissingInformation());
    }

    public function testSetAndGetValueFor(): void
    {
        $ic = new InformationCollector();
        $ic->registerRequest(new InformationRequest('foo'));
        $ic->setValueFor('foo', 'bar');
        self::assertEquals('bar', $ic->getValueFor('foo'));
    }

    public function testGetValueForWithDefault(): void
    {
        $ic = new InformationCollector();
        self::assertEquals('bar', $ic->getValueFor('foo', 'bar'));
    }
}
