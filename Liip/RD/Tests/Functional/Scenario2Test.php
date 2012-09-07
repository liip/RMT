<?php

namespace Liip\RD\Tests\Functional;


class Scenario2Test extends \PHPUnit_Framework_TestCase
{
    protected $scenarioDir;

    protected function setUp()
    {
//        $this->scenarioDir = __DIR__.'/scenarios/2_changelog_simple';
//        chdir($this->scenarioDir);
    }

    public function testRelease(){
        $this->markTestSkipped("Until we have --comment option on the task");
        exec('RD release --comment="test"');
        $expectedChangelog = <<<CHANGELOG
    06/09/2012 10:16  2  test
    06/09/2012 08:34  1  First version
CHANGELOG;
        $this->assertEquals($expectedChangelog, file_get_contents($this->scenarioDir.'/CHANGELOG'));
    }

}
