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

}

