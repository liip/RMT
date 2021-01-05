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

class CurrentCommandTest extends RMTFunctionalTestBase
{
    public function testNormal(): void
    {
        $this->initGit();
        $this->createConfig('simple', 'vcs-tag', array('vcs' => 'git'));
        exec('git tag 4');
        self::assertEquals('Current release is: 4', exec('./RMT current --no-ansi'));
    }

    public function testRaw(): void
    {
        $this->initGit();
        $this->createConfig('semantic', 'vcs-tag', array('vcs' => 'git'));
        exec('git tag 2.3.4');
        self::assertEquals('2.3.4', exec('./RMT current --raw'));
    }

    public function testVCSTag(): void
    {
        $this->initGit();
        $this->createConfig('semantic', array('name' => 'vcs-tag', 'tag-prefix' => 'toto_'), array('vcs' => 'git'));
        exec('git tag toto_2.3.4');
        self::assertEquals('2.3.4', exec('./RMT current --raw'));
        self::assertEquals('toto_2.3.4', exec('./RMT current --raw --vcs-tag'));
    }

    public function testNumericCompare(): void
    {
        $this->initGit();
        $this->createConfig('semantic', 'vcs-tag', array('vcs' => 'git'));
        exec('git tag 1.3.11');
        exec('git tag 1.3.10');
        exec('git tag 1.3.1');
        self::assertEquals('1.3.11', exec('./RMT current --raw'));
    }
}
