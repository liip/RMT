<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Functional;

class GitTest extends RMTFunctionalTestBase
{
    public function testInitialVersion(): void
    {
        $this->initGit();
        $this->createConfig('simple', 'vcs-tag', array('vcs' => 'git'));
        exec('./RMT release -n --confirm-first');
        exec('git tag', $tags);
//        $this->manualDebug();
        self::assertEquals(array('1'), $tags);
    }

    public function testInitialVersionSemantic(): void
    {
        $this->initGit();
        $this->createConfig('semantic', 'vcs-tag', array('vcs' => 'git'));
        exec('./RMT release -n  --type=patch --confirm-first');
        exec('git tag', $tags);
//        $this->manualDebug();
        self::assertEquals(array('0.0.1'), $tags);
    }

    public function testSimple(): void
    {
        $this->initGit();
        exec('git tag 1');
        exec('git tag 3');
        exec('git tag toto');
        $this->createConfig('simple', 'vcs-tag', array('vcs' => 'git'));
        exec('./RMT release -n');
        exec('git tag', $tags);
//        $this->manualDebug();
        self::assertEquals(array('1', '3', '4', 'toto'), $tags);
    }

    public function testSemantic(): void
    {
        $this->initGit();
        exec('git tag 2.1.19');
        $this->createConfig('semantic', 'vcs-tag', array('vcs' => 'git'));
        exec('./RMT release -n --type=minor');
        exec('git tag', $tags);
//        $this->manualDebug();
        self::assertEquals(array('2.1.19', '2.2.0'), $tags);
    }

    public function testTagPrefix(): void
    {
        $this->initGit();
        exec('git tag 2');
        exec('git tag v_1');
        $this->createConfig('simple', array('name' => 'vcs-tag', 'tag-prefix' => 'v_'), array('vcs' => 'git'));
        exec('./RMT release -n');
        exec('git tag', $tags);
//        $this->manualDebug();
        self::assertEquals(array('2', 'v_1', 'v_2'), $tags);
    }

    public function testTagPrefixWithBranchNamePlaceHolder(): void
    {
        $this->initGit();
        $this->createConfig('simple', array('name' => 'vcs-tag', 'tag-prefix' => '_{branch-name}_'), array('vcs' => 'git'));
        exec('./RMT release -n --confirm-first');
        exec('git tag', $tags);
        self::assertEquals(array('_main_1'), $tags);
    }
}
