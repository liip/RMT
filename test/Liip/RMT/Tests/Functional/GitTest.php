<?php

namespace Liip\RMT\Tests\Functional;


class GitTest extends RMTFunctionalTestBase
{

    public function testInitialVersion(){
        $this->initGit();
        $this->createJsonConfig('simple', 'vcs-tag', array('vcs'=>'git'));
        exec('./RMT release -n --confirm-first');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('1'), $tags);
    }

    public function testInitialVersionSemantic(){
        $this->initGit();
        $this->createJsonConfig('semantic', 'vcs-tag', array('vcs'=>'git'));
        exec('./RMT release -n  --type=patch --confirm-first');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('0.0.1'), $tags);
    }


    public function testSimple(){
        $this->initGit();
        exec('git tag 1');
        exec('git tag 3');
        exec('git tag toto');
        $this->createJsonConfig('simple', 'vcs-tag', array('vcs'=>'git'));
        exec('./RMT release -n');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('1','3', '4', 'toto'), $tags);
    }

    public function testSemantic()
    {
        $this->initGit();
        exec('git tag 2.1.19');
        $this->createJsonConfig('semantic', 'vcs-tag', array('vcs'=>'git'));
        exec('./RMT release -n --type=minor');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('2.1.19', '2.2.0'), $tags);
    }

    public function testTagPrefix(){
        $this->initGit();
        exec('git tag 2');
        exec('git tag v_1');
        $this->createJsonConfig('simple', array('name'=>'vcs-tag', 'tag-prefix'=>'v_'), array('vcs'=>'git'));
        exec('./RMT release -n');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('2','v_1', 'v_2'), $tags);
    }

    public function testTagPrefixWithBranchNamePlaceHolder(){
        $this->initGit();
        $this->createJsonConfig('simple', array('name'=>'vcs-tag', 'tag-prefix'=>'_{branch-name}_'), array('vcs'=>'git'));
        exec('./RMT release -n --confirm-first');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('_master_1'), $tags);
    }

    protected function initGit()
    {
        exec('git init');
        exec('git add *');
        exec('git commit -m "First commit"');
    }

}
