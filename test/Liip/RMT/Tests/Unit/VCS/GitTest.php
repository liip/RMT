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

use Liip\RMT\Exception;
use Liip\RMT\VCS\Git;
use PHPUnit\Framework\TestCase;

class GitTest extends TestCase
{
    protected $testDir;

    protected function setUp(): void
    {
        // Create a temp folder and extract inside the git test folder
        $tempDir = tempnam(sys_get_temp_dir(), '');
        if (file_exists($tempDir)) {
            unlink($tempDir);
        }
        mkdir($tempDir);
        chdir($tempDir);
        exec('unzip ' . __DIR__ . '/gitRepo.zip');
        exec('git reset --hard');
        $this->testDir = $tempDir;
    }

    public function testGetAllModificationsSince(): void
    {
        $vcs = new Git();
        $modifs = $vcs->getAllModificationsSince('1.1.0');
        self::assertStringContainsString('Add a third file', $modifs[0]);
        self::assertStringContainsString('Modification of the first file', $modifs[1]);
    }

    public function testGetModifiedFilesSince(): void
    {
        $vcs = new Git();
        $files = $vcs->getModifiedFilesSince('1.1.0');
        self::assertEquals(['file1' => 'M', 'file3' => 'A'], $files);
    }

    public function testGetLocalModifications(): void
    {
        $vcs = new Git();
        exec('touch foo');
        $modifs = $vcs->getLocalModifications();
        self::assertStringContainsString('foo', implode($modifs));
    }

    public function testGetTags(): void
    {
        $vcs = new Git();
        self::assertEquals(['1.0.0', '1.1.0'], $vcs->getTags());
    }

    public function testCreateTag(): void
    {
        $vcs = new Git();
        $vcs->createTag('2.0.0');
        self::assertEquals(['1.0.0', '1.1.0', '2.0.0'], $vcs->getTags());
    }

    public function testSaveWorkingCopy(): void
    {
        $vcs = new Git();
        $vcs->createTag('2.0.0');
        self::assertEquals([], $vcs->getAllModificationsSince('2.0.0'));
        exec('rm file2');
        $vcs->saveWorkingCopy('Remove the second file');
        self::assertCount(1, $vcs->getAllModificationsSince('2.0.0'));
    }

    public function testGetCurrentBranch(): void
    {
        $vcs = new Git();
        self::assertEquals('master', $vcs->getCurrentBranch());
        system('git checkout -b foo --quiet');
        self::assertEquals('foo', $vcs->getCurrentBranch());
        exec('git checkout master --quiet');
        self::assertEquals('master', $vcs->getCurrentBranch());
    }

    public function testGetCurrentBranchWhenNotInBranch(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Not currently on any branch');

        $vcs = new Git();
        exec('git checkout 9aca70b --quiet');
        $vcs->getCurrentBranch();
    }

    public function testChangeNoMergeCommits(): void
    {
        $vcs = new Git();
        exec('git checkout -b merge-branch --quiet');
        exec('echo "text" > new-file && git add -A && git commit -m "First commit"');
        exec('git checkout master --quiet');
        exec('git merge --no-ff merge-branch');

        $modifs = $vcs->getAllModificationsSince('1.1.0', false, true);

        self::assertStringContainsString('First commit', $modifs[0]);
        self::assertStringContainsString('Add a third file', $modifs[1]);
        self::assertStringContainsString('Modification of the first file', $modifs[2]);
    }

    public function testChangeWithMergeCommits(): void
    {
        $vcs = new Git();
        exec('git checkout -b merge-branch --quiet');
        exec('echo "text" > new-file && git add -A && git commit -m "First commit"');
        exec('git checkout master --quiet');
        exec('git merge --no-ff merge-branch');

        $modifs = $vcs->getAllModificationsSince('1.1.0');

        self::assertStringContainsString("Merge branch 'merge-branch'", $modifs[0]);
        self::assertStringContainsString('First commit', $modifs[1]);
        self::assertStringContainsString('Add a third file', $modifs[2]);
        self::assertStringContainsString('Modification of the first file', $modifs[3]);
    }

    protected function tearDown(): void
    {
        // Remove the test folder
        exec('rm -rf ' . $this->testDir);
        chdir(__DIR__);
    }
}
