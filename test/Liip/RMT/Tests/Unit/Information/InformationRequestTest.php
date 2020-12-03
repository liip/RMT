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

use InvalidArgumentException;
use Liip\RMT\Information\InformationRequest;
use PHPUnit\Framework\TestCase;

class InformationRequestTest extends TestCase
{
    public function testSetAndGetValue(): void
    {
        $ir = new InformationRequest('foo', ['type' => 'text']);
        self::assertFalse($ir->hasValue());
        $ir->setValue('bar');
        self::assertTrue($ir->hasValue());
        self::assertEquals('bar', $ir->getValue());
    }

    /**
     * @dataProvider getDataForValidationSuccess
     */
    public function testValidationSuccess(array $options, $value, ?string $expected = null): void
    {
        if (func_num_args() === 2) {
            $expected = $value;
        }
        $ir = new InformationRequest('foo', $options);
        $ir->setValue($value);
        self::assertEquals($expected, $ir->getValue());
    }

    public function getDataForValidationSuccess(): array
    {
        return [
            [['type' => 'text'], 'string'],
            [['type' => 'yes-no'], 'y'],
            [['type' => 'yes-no'], 'n'],
            [['type' => 'yes-no'], 'yes', 'y'],
            [['type' => 'yes-no'], 'no' , 'n'],
            [['type' => 'choice', 'choices' => ['apple', 'banana', 'cherry']], 'apple'],
            [['type' => 'confirmation'], true],
            [['type' => 'confirmation'], false],
        ];
    }

    /**
     * @dataProvider getDataForValidationFail
     */
    public function testValidationFail(array $options, $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $ir = new InformationRequest('foo', $options);
        $ir->setValue($value);
    }

    public function getDataForValidationFail(): array
    {
        $choices = ['apple', 'banana', 'cherry'];

        return [
            [['type' => 'text'], true],
            [['type' => 'text'], ''],
            [['type' => 'yes-no'], 'foo'],
            [['type' => 'yes-no'], 19],
            [['type' => 'choice', 'choices' => $choices], 'mango'],
        ];
    }
}
