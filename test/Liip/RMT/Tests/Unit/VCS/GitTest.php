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

use Liip\RMT\VCS\Git;

class GitTest extends \PHPUnit_Framework_TestCase
{
    protected $testDir;

    protected function setUp()
    {
        // Create a temp folder and extract inside the git test folder
        $tempDir = tempnam(sys_get_temp_dir(), '');
        if (file_exists($tempDir)) {
            unlink($tempDir);
        }
        mkdir($tempDir);
        chdir($tempDir);
        exec('unzip ' . __DIR__ . '/gitRepo.zip');
        exec('git checkout .');
        $this->testDir = $tempDir;
    }

    public function testGetAllModificationsSince()
    {
        $vcs = new Git();
        $modifs = $vcs->getAllModificationsSince('1.1.0');
        $this->assertContains('Add a third file', $modifs[0]);
        $this->assertContains('Modification of the first file', $modifs[1]);
    }

    public function testGetModifiedFilesSince()
    {
        $vcs = new Git();
        $files = $vcs->getModifiedFilesSince('1.1.0');
        $this->assertEquals(array('file1' => 'M', 'file3' => 'A'), $files);
    }

    public function testGetLocalModifications()
    {
        $vcs = new Git();
        exec('touch foo');
        $modifs = $vcs->getLocalModifications();
        $this->assertContains('foo', implode($modifs));
    }

    public function testGetTags()
    {
        $vcs = new Git();
        $this->assertEquals(array('1.0.0', '1.1.0'), $vcs->getTags());
    }

    /**
     * @dataProvider invalidTagNames
     * @expectedException \Liip\RMT\Exception\InvalidTagNameException
     */
    public function testInvalidateTag($tag)
    {
        $vcs = new Git();
        $vcs->validateTag($tag);
    }

    public function invalidTagNames()
    {
        return array(
            array("test..test"),
            array("test*test"),
            array('test[test'),
            array("test?test"),
            array("test\ntest"),
            array("test\rtest"),
            array("test."),
            array("@"),
            array("\\"),
        );
    }

    /**
     * @dataProvider validTagNames
     */
    public function testValidateTag($tag)
    {
        $vcs = new Git();
        $vcs->validateTag($tag);
    }

    public function validTagNames()
    {
        return array(
            array('test/test'),
            array('test'),
            array(2345),
            array('1.2'),
            array('1.2.3'),
            array('v1.2'),
            array('v1.2.3'),
        );
    }

    public function testCreateTag()
    {
        $vcs = new Git();
        $vcs->createTag('2.0.0');
        $this->assertEquals(array('1.0.0', '1.1.0', '2.0.0'), $vcs->getTags());
    }

    public function testSaveWorkingCopy()
    {
        $vcs = new Git();
        $vcs->createTag('2.0.0');
        $this->assertEquals(array(), $vcs->getAllModificationsSince('2.0.0'));
        exec('rm file2');
        $vcs->saveWorkingCopy('Remove the second file');
        $this->assertCount(1, $vcs->getAllModificationsSince('2.0.0'));
    }

    public function testGetCurrentBranch()
    {
        $vcs = new Git();
        $this->assertEquals('master', $vcs->getCurrentBranch());
        system('git checkout -b foo --quiet');
        $this->assertEquals('foo', $vcs->getCurrentBranch());
        exec('git checkout master --quiet');
        $this->assertEquals('master', $vcs->getCurrentBranch());
    }

    /**
     * @expectedException \Liip\RMT\Exception
     * @expectedExceptionMessage Not currently on any branch
     */
    public function testGetCurrentBranchWhenNotInBranch()
    {
        $vcs = new Git();
        exec('git checkout 9aca70b --quiet');
        $vcs->getCurrentBranch();
    }

    public function testChangeNoMergeCommits()
    {
        $vcs = new Git();
        exec('git checkout -b merge-branch --quiet');
        exec('echo "text" > new-file && git add -A && git commit -m "First commit"');
        exec('git checkout master --quiet');
        exec('git merge --no-ff merge-branch');

        $modifs = $vcs->getAllModificationsSince('1.1.0', false, true);

        $this->assertContains('First commit', $modifs[0]);
        $this->assertContains('Add a third file', $modifs[1]);
        $this->assertContains('Modification of the first file', $modifs[2]);
    }

    public function testChangeWithMergeCommits()
    {
        $vcs = new Git();
        exec('git checkout -b merge-branch --quiet');
        exec('echo "text" > new-file && git add -A && git commit -m "First commit"');
        exec('git checkout master --quiet');
        exec('git merge --no-ff merge-branch');

        $modifs = $vcs->getAllModificationsSince('1.1.0');

        $this->assertContains("Merge branch 'merge-branch'", $modifs[0]);
        $this->assertContains('First commit', $modifs[1]);
        $this->assertContains('Add a third file', $modifs[2]);
        $this->assertContains('Modification of the first file', $modifs[3]);
    }

    protected function tearDown()
    {
        // Remove the test folder
        exec('rm -rf ' . $this->testDir);
        chdir(__DIR__);
    }
}
