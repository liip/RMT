<?php

namespace Liip\RD\Tests\Functional;


class Scenario3Test extends \PHPUnit_Framework_TestCase
{
    protected $scenarioDir;

    protected function setUp()
    {
        $this->scenarioDir = __DIR__.'/scenarios/3_git_semantic';
        chdir($this->scenarioDir);
        exec('chmod +x RD');
        exec('git init');
        exec('git add *');
        exec('git commit -m "First commit"');
        exec('git tag 2.1.19');
    }

    public function testRelease()
    {
        exec('./RD release --type=minor');
        exec('git tag', $tags);
        $this->assertEquals(array('2.1.19', '2.2.0'), $tags);
    }

    public function tearDown()
    {
        exec('rm -rf '.$this->scenarioDir.'/.git');
        exec('git checkout .');
    }

}
