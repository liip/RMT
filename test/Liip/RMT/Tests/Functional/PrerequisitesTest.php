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

class PrerequisitesTest extends RMTFunctionalTestBase
{
    public function testDisplayLastChange(): void
    {
        $this->createConfig('simple', 'vcs-tag', array(
            'prerequisites' => array('display-last-changes'),
            'vcs' => 'git',
        ));
        $this->initGit();
        exec('git tag 1');
        exec('echo "foo" > fileFoo');
        exec('git add fileFoo');
        exec('git commit -m "Add a simple file"');
        exec('git mv fileFoo fileBar');
        exec('git commit -m "Rename foo to bar"');

        exec('./RMT release -n', $consoleOutput, $exitCode);
        $consoleOutput = implode("\n", $consoleOutput);
        self::assertStringNotContainsString('First commit', $consoleOutput);
        self::assertStringContainsString('Add a simple file', $consoleOutput);
        self::assertStringContainsString('Rename foo to bar', $consoleOutput);
    }

    public function testWorkingCopyCheckFailsWithLocalModifications(): void
    {
        $this->createConfig('simple', 'vcs-tag', array(
            'prerequisites' => array('working-copy-check'),
            'vcs' => 'git',
        ));
        $this->initGit();
        exec('git tag 1');

        // Release blocked by the check
        exec('touch toto');
        exec('./RMT release -n 2>&1', $consoleOutput, $exitCode);
        self::assertGreaterThan(0, $exitCode);
        self::assertStringContainsString('local modification', implode("\n", $consoleOutput));
    }

    public function testWorkingCopyContinuesWithAllowedModifications(): void
    {
        $this->createConfig('simple', 'vcs-tag', array(
            'prerequisites' => array('working-copy-check'=>array('allowed-modifications'=>array('CHANGELOG'))),
            'vcs' => 'git',
        ));
        $this->initGit();
        exec('git tag 1');

        // Release should continue even though file CHANGELOG has been modified.
        exec('echo toto >> CHANGELOG');
        exec('./RMT release -n 2>&1', $consoleOutput, $exitCode);
        self::assertEquals(0, $exitCode, implode(PHP_EOL, $consoleOutput));
        exec('git tag', $tags2);
        self::assertEquals(array('1', '2'), $tags2);
    }

    public function testWorkingCopyWithIgnoreCheck(): void
    {
        $this->createConfig('simple', 'vcs-tag', array(
            'prerequisites' => array('working-copy-check'=>array('allow-ignore'=>true)),
            'vcs' => 'git',
        ));
        $this->initGit();
        exec('git tag 1');

        // Release working, check is ignore
        exec('./RMT release -n --ignore-check', $consoleOutput, $exitCode);
        self::assertEquals(0, $exitCode);
        exec('git tag', $tags);
        self::assertEquals(array('1', '2'), $tags);
    }

    public function testWorkingCopy(): void
    {
        $this->createConfig('simple', 'vcs-tag', array(
            'prerequisites' => array('working-copy-check'),
            'vcs' => 'git',
        ));
        $this->initGit();
        exec('git tag 1');

//        $this->manualDebug();

        // Normal case, check is passing
        exec('./RMT release -n', $consoleOutput, $exitCode);
        self::assertEquals(0, $exitCode, implode(PHP_EOL, $consoleOutput));
        exec('git tag', $tags2);
        self::assertEquals(array('1', '2'), $tags2);
    }
}
