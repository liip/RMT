<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Unit\Changelog\Formatter;

use Liip\RMT\Changelog\Formatter\SemanticChangelogFormatter;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;

class SemanticChangelogFormatterTest extends TestCase
{
    protected function getFormatter(): SemanticChangelogFormatter
    {
        $formatter = $this
            ->getMockBuilder(SemanticChangelogFormatter::class)
            ->{method_exists(MockBuilder::class, 'onlyMethods') ? 'onlyMethods' : 'setMethods'}(['getFormattedDate'])
            ->getMock()
        ;

        $formatter
            ->method('getFormattedDate')
            ->willReturn('08/11/1980 12:34')
        ;

        return $formatter;
    }

    /**
     * @dataProvider getDataForFirstReleaseTest
     */
    public function testFirstRelease($version, $type, $results): void
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines([], $version, 'foo bar', ['type' => $type]);
        self::assertEquals($results, $lines);
    }

    public function getDataForFirstReleaseTest(): array
    {
        return [
            ['0.0.1', 'patch', ['', 'VERSION 0  FOO BAR', '==================', '', '   Version 0.0 - foo bar', '      08/11/1980 12:34  0.0.1  initial release']],
            ['0.1.0', 'patch', ['', 'VERSION 0  FOO BAR', '==================', '', '   Version 0.1 - foo bar', '      08/11/1980 12:34  0.1.0  initial release']],
            ['1.0.0', 'patch', ['', 'VERSION 1  FOO BAR', '==================', '', '   Version 1.0 - foo bar', '      08/11/1980 12:34  1.0.0  initial release']],
        ];
    }

    public function testExtraLines(): void
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines([
            '',
            'VERSION 1  FOO BAR',
            '==================',
            '',
            '   Version 1.0 - foo bar',
            '      08/11/1980 12:34  1.0.0  initial release',

        ], '1.0.1', 'foo bar', ['type' => 'patch', 'extra-lines' => [
            'ada96f3 Add new tests for command RMT init and RMT current ref #10',
            '2eb6fae Documentation review',
        ]]);

        self::assertEquals([
            '',
            'VERSION 1  FOO BAR',
            '==================',
            '',
            '   Version 1.0 - foo bar',
            '      08/11/1980 12:34  1.0.1  foo bar',
            '         ada96f3 Add new tests for command RMT init and RMT current ref #10',
            '         2eb6fae Documentation review',
            '      08/11/1980 12:34  1.0.0  initial release',
        ], $lines);
    }

    public function testUpdateExistingWithPatch(): void
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(
            [
                '',
                'VERSION 1  FOO BAR',
                '==================',
                '',
                '   Version 1.0 - foo bar',
                '      08/11/1980 12:34  1.0.0  initial release',
            ],
            '1.0.1',
            'foofoo',
            ['type' => 'patch']
        );

        self::assertEquals([
            '',
            'VERSION 1  FOO BAR',
            '==================',
            '',
            '   Version 1.0 - foo bar',
            '      08/11/1980 12:34  1.0.1  foofoo',
            '      08/11/1980 12:34  1.0.0  initial release',
        ], $lines);
    }

    public function testUpdateExistingWithMinor(): void
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(
            [
                '',
                'VERSION 1  FOO BAR',
                '==================',
                '',
                '   Version 1.0 - foo bar',
                '      08/11/1980 12:34  1.0.0  initial release',
            ],
            '1.1.0',
            'foofoo',
            ['type' => 'minor']
        );
        self::assertEquals([
            '',
            'VERSION 1  FOO BAR',
            '==================',
            '',
            '   Version 1.1 - foofoo',
            '      08/11/1980 12:34  1.1.0  initial release',
            '',
            '   Version 1.0 - foo bar',
            '      08/11/1980 12:34  1.0.0  initial release',
        ], $lines);
    }

    public function testUpdateExistingWithMajor(): void
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(
            [
                '',
                'VERSION 1  FOO BAR',
                '==================',
                '',
                '   Version 1.0 - foo bar',
                '      08/11/1980 12:34  1.0.0  initial release',
            ],
            '2.0.0',
            'foofoo',
            ['type' => 'major']
        );

        self::assertEquals([
            '',
            'VERSION 2  FOOFOO',
            '=================',
            '',
            '   Version 2.0 - foofoo',
            '      08/11/1980 12:34  2.0.0  initial release',
            '',
            'VERSION 1  FOO BAR',
            '==================',
            '',
            '   Version 1.0 - foo bar',
            '      08/11/1980 12:34  1.0.0  initial release',
        ], $lines);
    }
}
