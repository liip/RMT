<?php

namespace Liip\RD\Tests\Functional;


class CurrentCommandTest extends RDFunctionalTestBase
{
    public function testNormal()
    {
        $this->initGit();
        $this->createJsonConfig('simple', 'vcs-tag', array('vcs'=>'git'));
        exec('git tag 4');
        $this->assertEquals("Current release is: 4", exec('./RD current'));
    }

    public function testRaw()
    {
        $this->initGit();
        $this->createJsonConfig('semantic', 'vcs-tag', array('vcs'=>'git'));
        exec('git tag 2.3.4');
        $this->assertEquals("2.3.4", exec('./RD current --raw'));
    }

    public function testVCSTag()
    {
        $this->initGit();
        $this->createJsonConfig('semantic', array('name'=>'vcs-tag', 'tag-prefix'=>'toto_'), array('vcs'=>'git'));
        exec('git tag toto_2.3.4');
        $this->assertEquals("2.3.4", exec('./RD current --raw'));
        $this->assertEquals("toto_2.3.4", exec('./RD current --raw --vcs-tag'));
    }

    public function testNumericCompare()
    {
        $this->initGit();
        $this->createJsonConfig('semantic', 'vcs-tag', array('vcs'=>'git'));
        exec('git tag 1.3.11');
        exec('git tag 1.3.10');
        exec('git tag 1.3.1');
        $this->assertEquals("1.3.11", exec('./RD current --raw'));
    }

}

