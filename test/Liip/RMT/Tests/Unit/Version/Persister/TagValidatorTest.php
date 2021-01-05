<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Version\Persister;

use Liip\RMT\Version\Persister\TagValidator;
use PHPUnit\Framework\TestCase;

class TagValidatorTest extends TestCase
{
    /**
     * @dataProvider getTagData
     */
    public function testIsValid(string $tag, bool $result, string $regex, string $tagPrefix = ''): void
    {
        $validator = new TagValidator($regex, $tagPrefix);
        self::assertEquals($result, $validator->isValid($tag));
    }

    public function getTagData(): array
    {
        $simpleRegEx = '\d+';
        $semanticRegEx = '\d+\.\d+\.\d+';

        return [
            ['1', true, $simpleRegEx],
            ['23', true, $simpleRegEx],
            ['3d', false, $simpleRegEx],
            ['v_23', true, $simpleRegEx, 'v_'],
            ['v-23',  false, $simpleRegEx, 'v_'],
            ['v_3d',  false, $simpleRegEx, 'v_'],
            ['1.0.3', true, $semanticRegEx],
            ['3.0.3.7', false, $semanticRegEx],
            ['3.b.3',  false, $semanticRegEx],
            ['dev_3.3.3', true, $semanticRegEx, 'dev_'],
            ['dev_3.3.3.7', false, $semanticRegEx, 'dev_'],
        ];
    }

    public function testFiltrateList(): void
    {
        $validator = new TagValidator('\d');
        self::assertEquals(
            ['1', '3'],
            $validator->filtrateList(['a', '1', '3s', '3'])
        );
    }
}
