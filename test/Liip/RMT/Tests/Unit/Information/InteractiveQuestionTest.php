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

use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Information\InteractiveQuestion;
use PHPUnit\Framework\TestCase;

class InteractiveQuestionTest extends TestCase
{
    public function testGetDefault(): void
    {
        $iq = new InteractiveQuestion(new InformationRequest('foo'));
        self::assertFalse($iq->hasDefault());

        $iq = new InteractiveQuestion(new InformationRequest('foo', ['default' => 'bar']));
        self::assertEquals('bar', $iq->getDefault());

        $iq = new InteractiveQuestion(new InformationRequest('fruit', [
            'type' => 'choice',
            'choices' => ['apple', 'banana', 'cherry'],
            'choices_shortcuts' => ['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'],
            'default' => 'banana',
        ]));
        self::assertEquals('b', $iq->getDefault());
    }

    public function testValidateChoicesWithShortcuts(): void
    {
        $ir = new InformationRequest('fruit', [
            'type' => 'choice',
            'choices' => ['apple', 'banana', 'cherry'],
            'choices_shortcuts' => ['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'],
        ]);

        $iq = new InteractiveQuestion($ir);
        self::assertEquals('apple', $iq->validate('a'));
    }
}
