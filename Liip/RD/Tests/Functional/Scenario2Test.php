<?php

namespace Liip\RD\Tests\Functional;


class Scenario2Test extends \PHPUnit_Framework_TestCase
{
    protected $scenarioDir;

    protected function setUp()
    {
        $this->scenarioDir = __DIR__.'/scenarios/2_changelog_simple';
        chdir($this->scenarioDir);
        exec('chmod +x RD');
    }

    public function testRelease(){
        exec('./RD release --comment="test"');
        $changelogLines = file($this->scenarioDir.'/CHANGELOG', FILE_IGNORE_NEW_LINES);

        $this->assertRegExp('/2\s\stest/', $changelogLines[2]);
    }

    public function tearDown()
    {
        exec('git checkout .');
    }

}
