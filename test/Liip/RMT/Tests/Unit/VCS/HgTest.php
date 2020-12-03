<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Unit\Version;

use Liip\RMT\VCS\Hg;
use PHPUnit\Framework\TestCase;

class HgTest extends TestCase
{
    protected $testDir;

    protected function setUp(): void
    {
        // Create a temp folder and extract inside the Hg test folder
        $tempDir = tempnam(sys_get_temp_dir(), '');
        if (file_exists($tempDir)) {
            unlink($tempDir);
        }
        mkdir($tempDir);
        chdir($tempDir);
        exec('unzip ' . __DIR__ . '/HgRepo.zip');
        exec('hg update');
        $this->testDir = $tempDir;
    }

    public function testGetAllModificationsSince(): void
    {
        $vcs = new Hg();
        $modifs = $vcs->getAllModificationsSince('1.1.0');
        self::assertStringContainsString('Add a third file', $modifs[0]);
        self::assertStringContainsString('Modification of the first file', $modifs[1]);
    }

    public function testGetModifiedFilesSince(): void
    {
        $vcs = new Hg();
        $files = $vcs->getModifiedFilesSince('1.1.0');
        self::assertEquals(array('file1' => 'M', 'file3' => 'A', '.hgtags' => 'M'), $files);
    }

    public function testGetLocalModifications(): void
    {
        $vcs = new Hg();
        exec('touch foo');
        $modifs = $vcs->getLocalModifications();
        self::assertStringContainsString('foo', implode($modifs));
    }

    public function testGetTags(): void
    {
        $vcs = new Hg();
        self::assertEquals(['tip', '1.1.0', '1.0.0'], $vcs->getTags());
    }

    public function testCreateTag(): void
    {
        $vcs = new Hg();
        $vcs->createTag('2.0.0');
        self::assertEquals(['tip', '2.0.0', '1.1.0', '1.0.0'], $vcs->getTags());
    }

    public function testSaveWorkingCopy(): void
    {
        $vcs = new Hg();
        $vcs->createTag('2.0.0');
        self::assertCount(1, $vcs->getAllModificationsSince('2.0.0'));
        exec('rm file2');
        $vcs->saveWorkingCopy('Remove the second file');
        self::assertCount(2, $vcs->getAllModificationsSince('2.0.0'));
    }

    public function testGetCurrentBranch(): void
    {
        $vcs = new Hg();
        self::assertEquals('default', $vcs->getCurrentBranch());
        system('hg branch -q foo');
        self::assertEquals('foo', $vcs->getCurrentBranch());
        exec('hg branch -q default');
        self::assertEquals('default', $vcs->getCurrentBranch());
    }

    protected function tearDown(): void
    {
        // Remove the test folder
        // exec('rm -rf '.$this->testDir);
        chdir(__DIR__);
    }
}
