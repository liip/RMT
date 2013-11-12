<?php

namespace Liip\RMT\Tests\Functional;


class GitTest extends RMTFunctionalTestBase
{

    public function testInitialVersion(){
        $this->initGit();
        $this->createConfig('simple', 'vcs-tag', array('vcs'=>'git'));
        exec('./RMT release -n --confirm-first');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('1'), $tags);
    }

    public function testInitialVersionSemantic(){
        $this->initGit();
        $this->createConfig('semantic', 'vcs-tag', array('vcs'=>'git'));
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
        $this->createConfig('simple', 'vcs-tag', array('vcs'=>'git'));
        exec('./RMT release -n');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('1','3', '4', 'toto'), $tags);
    }

    public function testSemantic()
    {
        $this->initGit();
        exec('git tag 2.1.19');
        $this->createConfig('semantic', 'vcs-tag', array('vcs'=>'git'));
        exec('./RMT release -n --type=minor');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('2.1.19', '2.2.0'), $tags);
    }

    public function testTagPrefix(){
        $this->initGit();
        exec('git tag 2');
        exec('git tag v_1');
        $this->createConfig('simple', array('name'=>'vcs-tag', 'tag-prefix'=>'v_'), array('vcs'=>'git'));
        exec('./RMT release -n');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('2','v_1', 'v_2'), $tags);
    }

    public function testTagPrefixWithBranchNamePlaceHolder(){
        $this->initGit();
        $this->createConfig('simple', array('name'=>'vcs-tag', 'tag-prefix'=>'_{branch-name}_'), array('vcs'=>'git'));
        exec('./RMT release -n --confirm-first');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('_master_1'), $tags);
    }

}
