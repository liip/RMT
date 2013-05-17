<?php

namespace Liip\RMT\Tests\Unit\Changelog\Formatter;

class SemanticChangelogFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Liip\RMT\Changelog\Formatter\SemanticChangelogFormatter
     */
    protected function getFormatter()
    {
        $formatter = $this->getMock('Liip\RMT\Changelog\Formatter\SemanticChangelogFormatter', array('getFormattedDate'));
        $formatter
            ->expects($this->any())
            ->method('getFormattedDate')
            ->will($this->returnValue('08/11/1980 12:34'))
        ;
        return $formatter;
    }

    /**
     * @dataProvider getDataForFirstReleaseTest
     */
    public function testFirstRelease($version, $type, $results)
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(array(), $version, 'foo bar', array('type'=>$type));
        $this->assertEquals($results, $lines);
    }

    public function getDataForFirstReleaseTest(){
        return array(
            array('0.0.1', 'patch', array('', 'VERSION 0  FOO BAR', '==================', '', '   Version 0.0 - foo bar', '      08/11/1980 12:34  0.0.1  initial release')),
            array('0.1.0', 'patch', array('', 'VERSION 0  FOO BAR', '==================', '', '   Version 0.1 - foo bar', '      08/11/1980 12:34  0.1.0  initial release')),
            array('1.0.0', 'patch', array('', 'VERSION 1  FOO BAR', '==================', '', '   Version 1.0 - foo bar', '      08/11/1980 12:34  1.0.0  initial release')),
        );
    }

    public function testExtraLines()
    {
        $formatter = $this->getFormatter();
        $lines = $formatter->updateExistingLines(array(
            '',
            'VERSION 1  FOO BAR',
            '==================',
            '',
            '   Version 1.0 - foo bar',
            '      08/11/1980 12:34  1.0.0  initial release'

        ), '1.0.1', 'foo bar', array('type'=>'patch', 'extra-lines' => array(
            'ada96f3 Add new tests for command RMT init and RMT current ref #10',
            '2eb6fae Documentation review'
        )));

        $this->assertEquals(array(
            '',
            'VERSION 1  FOO BAR',
            '==================',
            '',
            '   Version 1.0 - foo bar',
            '      08/11/1980 12:34  1.0.1  foo bar',
            '         ada96f3 Add new tests for command RMT init and RMT current ref #10',
            '         2eb6fae Documentation review',
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
