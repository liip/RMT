<?php

namespace Liip\RMT\Tests\Unit\Version;

use Liip\RMT\VCS\Hg;

class HgTest extends \PHPUnit_Framework_TestCase
{
    protected $testDir;

    protected function setUp()
    {
        // Create a temp folder and extract inside the Hg test folder
        $tempDir = tempnam(sys_get_temp_dir(),'');
        if (file_exists($tempDir)) {
            unlink($tempDir);
        }
        mkdir($tempDir);
        chdir($tempDir);
        exec('unzip '.__DIR__.'/HgRepo.zip');
        exec('hg update');
        $this->testDir = $tempDir;
    }

    public function testGetAllModificationsSince()
    {
        $vcs = new Hg();
        $modifs = $vcs->getAllModificationsSince('1.1.0');
        $this->assertContains("Add a third file", $modifs[0]);
        $this->assertContains("Modification of the first file", $modifs[1]);
    }

    public function testGetModifiedFilesSince() {
        $vcs = new Hg();
        $files = $vcs->getModifiedFilesSince('1.1.0');
        $this->assertEquals(array("file1" => "M", "file3" => "A", ".hgtags" => "M"), $files);
    }

    public function testGetLocalModifications()
    {
        $vcs = new Hg();
        exec('touch foo');
        $modifs = $vcs->getLocalModifications();
        $this->assertContains('foo', implode($modifs));
    }

    public function testGetTags()
    {
        $vcs = new Hg();
        $this->assertEquals(array("tip", "1.1.0","1.0.0"), $vcs->getTags());
    }

    public function testCreateTag()
    {
        $vcs = new Hg();
        $vcs->createTag('2.0.0');
        $this->assertEquals(array("tip","2.0.0","1.1.0", "1.0.0"), $vcs->getTags());
    }

    public function testSaveWorkingCopy()
    {
        $vcs = new Hg();
        $vcs->createTag('2.0.0');
        $this->assertCount(1, $vcs->getAllModificationsSince('2.0.0'));
        exec('rm file2');
        $vcs->saveWorkingCopy('Remove the second file');
        $this->assertCount(2,$vcs->getAllModificationsSince('2.0.0'));
    }

    public function testGetCurrentBranch()
    {
        $vcs = new Hg();
        $this->assertEquals('default', $vcs->getCurrentBranch());
        system("hg branch -q foo");
        $this->assertEquals('foo', $vcs->getCurrentBranch());
        exec("hg update -q default");
        $this->assertEquals('default', $vcs->getCurrentBranch());
    }

    protected function tearDown()
    {
        // Remove the test folder
        // exec('rm -rf '.$this->testDir);
        chdir(__DIR__);
    }


}
