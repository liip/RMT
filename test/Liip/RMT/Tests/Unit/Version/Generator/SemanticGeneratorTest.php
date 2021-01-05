<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Unit\Version;

use InvalidArgumentException;
use Liip\RMT\Version\Generator\SemanticGenerator;
use PHPUnit\Framework\TestCase;

class SemanticGeneratorTest extends TestCase
{
    /**
     * @dataProvider getVersionValues
     */
    public function testIncrement(string $current, string $type, string $label, string $result): void
    {
        $options = [
            'type' => $type,
            'label' => $label,
        ];

        $generator = new SemanticGenerator($options);
        self::assertEquals($result, $generator->generateNextVersion($current));
    }

    public function getVersionValues(): array
    {
        return [
            ['1.0.0',  'patch', 'none', '1.0.1'],
            ['1.23.0', 'minor', 'none', '1.24.0'],
            ['1.1.19', 'minor', 'none', '1.2.0'],
            ['1.0.0',  'major', 'none', '2.0.0'],
            ['1.19.3', 'major', 'none', '2.0.0'],
            ['3.3.3',  'major', 'none', '4.0.0'],
            ['3.3.3',  'major', 'alpha', '4.0.0-alpha'],
            ['4.0.0-aplha2',  'major', 'beta', '4.0.0-beta'],
            ['3.3.3',  'minor', 'beta', '3.4.0-beta'],
            ['4.0.0-beta',  'minor', 'beta', '4.0.0-beta2'],
            ['4.0.0',  'minor', 'rc', '4.1.0-rc'],
            ['4.0.0-rc',  'minor', 'none', '4.0.0'],
        ];
    }

    public function testIncrementWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The option [type] must be one of: {patch, minor, major}, "full" given');

        $generator = new SemanticGenerator(array('type' => 'full', 'label' => 'none'));
        $generator->generateNextVersion('1.0.0');
    }

    public function testCompare(): void
    {
        $generator = new SemanticGenerator();
        self::assertEquals(-1, $generator->compareTwoVersions('1.0.0', '1.0.1'));
        self::assertEquals(-1, $generator->compareTwoVersions('1.0.0-beta', '1.0.0'));
        self::assertEquals(0, $generator->compareTwoVersions('1.0.0', '1.0.0'));
        self::assertEquals(1, $generator->compareTwoVersions('1.0.1', '1.0.0'));
        self::assertEquals(1, $generator->compareTwoVersions('1.0.11', '1.0.1'));
        self::assertEquals(1, $generator->compareTwoVersions('1.0.1', '1.0.1-alpha'));
        self::assertEquals(1, $generator->compareTwoVersions('1.0.1-beta', '1.0.1-alpha'));
        self::assertEquals(1, $generator->compareTwoVersions('1.0.11-rc', '1.0.1-beta'));
        self::assertEquals(1, $generator->compareTwoVersions('1.0.2', '1.0.1-rc'));
    }
}
