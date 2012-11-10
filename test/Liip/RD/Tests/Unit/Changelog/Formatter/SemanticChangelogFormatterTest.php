<?php

namespace Liip\RD\Tests\Unit\Changelog\Formatter;

use Liip\RD\Changelog\Formatter\SemanticChangelogFormatter;

class SemanticChangelogFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Liip\RD\Changelog\Formatter\SemanticChangelogFormatter
     */
    protected function getFormatter()
    {
        $formatter = $this->getMock('Liip\RD\Changelog\Formatter\SemanticChangelogFormatter', array('getFormattedDate'));
        $formatter
            ->expects($this->any())
            ->method('getFormattedDate')
            ->will($this->returnValue('08/11/1980 12:34'))
        ;
        return $formatter;
    }

    public function testFirstPatch()
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(array(), '0.0.1', 'foo bar', array('type'=>'patch'));
        $this->assertCount(1, $lines);
        $this->assertEquals('      08/11/1980 12:34  0.0.1  foo bar', $lines[0]);
    }

    public function testFirstMinor()
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(array(), '0.1.0', 'foo bar', array('type'=>'minor'));
        $this->assertEquals(array(
            '',
            '   Version 0.1 - foo bar',
            '      08/11/1980 12:34  0.1.0  initial release'
        ), $lines);
    }

    public function testFirstMajor()
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(array(), '1.0.0', 'foo bar', array('type'=>'major'));
        $this->assertEquals(array(
            '',
            'VERSION 1  FOO BAR',
            '==================',
            '',
            '   Version 1.0 - foo bar',
            '      08/11/1980 12:34  1.0.0  initial release'

        ), $lines);
    }

    public function testUpdateExistingWithPatch()
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(
            array(
                '',
                'VERSION 1  FOO BAR',
                '==================',
                '',
                '   Version 1.0 - foo bar',
                '      08/11/1980 12:34  1.0.0  initial release'
            ),
            '1.0.1', 'foofoo', array('type'=>'patch')
        );

        $this->assertEquals(array(
            '',
            'VERSION 1  FOO BAR',
            '==================',
            '',
            '   Version 1.0 - foo bar',
            '      08/11/1980 12:34  1.0.1  foofoo',
            '      08/11/1980 12:34  1.0.0  initial release'
        ), $lines);
    }

    public function testUpdateExistingWithMinor()
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(
            array(
                '',
                'VERSION 1  FOO BAR',
                '==================',
                '',
                '   Version 1.0 - foo bar',
                '      08/11/1980 12:34  1.0.0  initial release'
            ),
            '1.1.0', 'foofoo', array('type'=>'minor')
        );
        $this->assertEquals(array(
            '',
            'VERSION 1  FOO BAR',
            '==================',
            '',
            '   Version 1.1 - foofoo',
            '      08/11/1980 12:34  1.1.0  initial release',
            '',
            '   Version 1.0 - foo bar',
            '      08/11/1980 12:34  1.0.0  initial release'
        ), $lines);
    }



    public function testUpdateExistingWithMajor()
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(
            array(
                '',
                'VERSION 1  FOO BAR',
                '==================',
                '',
                '   Version 1.0 - foo bar',
                '      08/11/1980 12:34  1.0.0  initial release'
            ),
            '2.0.0', 'foofoo', array('type'=>'major')
        );

        $this->assertEquals(array(
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
            '      08/11/1980 12:34  1.0.0  initial release'
        ), $lines);
    }

}
