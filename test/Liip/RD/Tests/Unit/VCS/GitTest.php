<?php

namespace Liip\RD\Tests\Unit\Version;

use Liip\RD\VCS\Git;

class GitTest extends \PHPUnit_Framework_TestCase
{
    protected $testDir;

    protected function setUp()
    {
        // Create a temp folder and extract inside the git test folder
        $tempDir = tempnam(sys_get_temp_dir(),'');
        if (file_exists($tempDir)) {
            unlink($tempDir);
        }
        mkdir($tempDir);
        chdir($tempDir);
        exec('unzip '.__DIR__.'/gitRepo.zip');
        exec('git checkout .');
        $this->testDir = $tempDir;
    }

    public function testGetAllModificationsSince()
    {
        $vcs = new Git();
        $modifs = $vcs->getAllModificationsSince('1.1.0');
        $this->assertEquals(array(
            "4c95178 Add a third file",
            "9aca70b Modification of the first file"
        ), $modifs);
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
        $this->assertEquals(array("1.0.0","1.1.0"), $vcs->getTags());
    }

    public function testCreateTag()
    {
        $vcs = new Git();
        $vcs->createTag('2.0.0');
        $this->assertEquals(array("1.0.0","1.1.0","2.0.0"), $vcs->getTags());
    }

    public function testSaveWorkingCopy()
    {
        $vcs = new Git();
        $vcs->createTag('2.0.0');
        $this->assertEquals(array(), $vcs->getAllModificationsSince('2.0.0'));
        exec('rm file2');
        $vcs->saveWorkingCopy('Remove the second file');
        $this->assertCount(1,$vcs->getAllModificationsSince('2.0.0'));
    }

    public function testGetCurrentBranch()
    {
        $vcs = new Git();
        $this->assertEquals('master', $vcs->getCurrentBranch());
    }


    protected function tearDown()
    {
        // Remove the test folder
        exec('rm -rf '.$this->testDir);
        chdir(__DIR__);
    }


}
