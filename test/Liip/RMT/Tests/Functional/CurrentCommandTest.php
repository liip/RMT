<?php

namespace Liip\RMT\Tests\Functional;


class CurrentCommandTest extends RMTFunctionalTestBase
{
    public function testNormal()
    {
        $this->initGit();
        $this->createConfig('simple', 'vcs-tag', array('vcs'=>'git'));
        exec('git tag 4');
        $this->assertEquals("Current release is: 4", exec('./RMT current --no-ansi'));
    }

    public function testRaw()
    {
        $this->initGit();
        $this->createConfig('semantic', 'vcs-tag', array('vcs'=>'git'));
        exec('git tag 2.3.4');
        $this->assertEquals("2.3.4", exec('./RMT current --raw'));
    }

    public function testVCSTag()
    {
        $this->initGit();
        $this->createConfig('semantic', array('name'=>'vcs-tag', 'tag-prefix'=>'toto_'), array('vcs'=>'git'));
        exec('git tag toto_2.3.4');
        $this->assertEquals("2.3.4", exec('./RMT current --raw'));
        $this->assertEquals("toto_2.3.4", exec('./RMT current --raw --vcs-tag'));
    }

    public function testNumericCompare()
    {
        $this->initGit();
        $this->createConfig('semantic', 'vcs-tag', array('vcs'=>'git'));
        exec('git tag 1.3.11');
        exec('git tag 1.3.10');
        exec('git tag 1.3.1');
        $this->assertEquals("1.3.11", exec('./RMT current --raw'));
    }

}

