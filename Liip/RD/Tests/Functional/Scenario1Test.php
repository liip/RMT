<?php

namespace Liip\RD\Tests\Functional;


class Scenario1Test extends \PHPUnit_Framework_TestCase
{
    protected $scenarioDir;

    protected function setUp()
    {
        $this->scenarioDir = __DIR__.'/scenarios/1_git_simple';
        chdir($this->scenarioDir);
        exec('git init');
        exec('git add *');
        exec('git commit -m "First commit"');
        exec('git tag 1');
        exec('git tag 3');
        exec('git tag toto');
    }

    public function testRelease(){
        exec('./RD release');
        exec('git tag', $tags);
        $this->assertEquals(array('1','3', '4', 'toto'), $tags);
    }

    public function tearDown()
    {
        exec('rm -rf '.$this->scenarioDir.'/.git');
    }

}
