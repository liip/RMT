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

class HgTest extends RMTFunctionalTestBase
{
    protected function setUp()
    {
        if (SKIP_HG_TESTS) {
            $this->markTestSkipped('HG is not installed');
        }

        parent::setUp();
    }


    public static function cleanTags($tags)
    {
        return array_map(function ($t) {
            $parts = explode(' ', $t);

            return $parts[0];
        }, $tags);
    }

    public function testInitialVersion()
    {
        $this->initHg();
        $this->createConfig('simple', 'vcs-tag', array('vcs' => 'hg'));
        exec('./RMT release -n --confirm-first');
        exec('hg tags', $tags);
        $this->assertEquals(array('tip', '1'), static::cleanTags($tags));
    }

    public function testInitialVersionSemantic()
    {
        $this->initHg();
        $this->createConfig('semantic', 'vcs-tag', array('vcs' => 'hg'));
        exec('./RMT release -n  --type=patch --confirm-first');
        exec('hg tags', $tags);
        $this->assertEquals(array('tip', '0.0.1'), static::cleanTags($tags));
    }

    public function testSimple()
    {
        $this->initHg();
        exec('hg tag 1');
        exec('hg tag 3');
        exec('hg tag toto');
        $this->createConfig('simple', 'vcs-tag', array('vcs' => 'hg'));
        exec('./RMT release -n');
        exec('hg tags', $tags);
        $this->assertEquals(array('tip', '4', 'toto', '3', '1'), static::cleanTags($tags));
    }

    public function testSemantic()
    {
        $this->initHg();
        exec('hg tag 2.1.19');
        $this->createConfig('semantic', 'vcs-tag', array('vcs' => 'hg'));
        exec('./RMT release -n --type=minor');
        exec('hg tags', $tags);
        $this->assertEquals(array('tip', '2.2.0', '2.1.19'), static::cleanTags($tags));
    }

    public function testTagPrefix()
    {
        $this->initHg();
        exec('hg tag 2');
        exec('hg tag v_1');
        $this->createConfig('simple', array('name' => 'vcs-tag', 'tag-prefix' => 'v_'), array('vcs' => 'hg'));
        exec('./RMT release -n');
        exec('hg tags', $tags);
        $this->assertEquals(array('tip', 'v_2', 'v_1', '2'), static::cleanTags($tags));
    }

    public function testTagPrefixWithBranchNamePlaceHolder()
    {
        $this->initHg();
        $this->createConfig('simple', array('name' => 'vcs-tag', 'tag-prefix' => '_{branch-name}_'), array('vcs' => 'hg'));
        exec('./RMT release -n --confirm-first');
        exec('hg tags', $tags);
        $this->assertEquals(array('tip', '_default_1'), static::cleanTags($tags));
    }

    protected function initHg()
    {
        exec('hg init');
        exec('echo "[ui]" > .hg/hgrc');
        exec('echo "username = John Doe <test@test.com>" >> .hg/hgrc');
        exec('hg add *');
        exec('hg commit -m "First commit"');
    }
}
