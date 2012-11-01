<?php

namespace Liip\RD\Tests\Unit\Changelog;

use Liip\RD\Changelog\ChangelogManager;

class ChangelogManagerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->file = tempnam(sys_get_temp_dir(),'CHANGELOG');
        touch($this->file);
    }

    /**
     * @expectedException \Liip\RD\Exception
     */
    public function testExceptionWhenNoFileFound()
    {
        unlink($this->file);
        new ChangelogManager($this->file);
    }

    public function testPatchVersion()
    {
        $manager = new ChangelogManager($this->file);
        $manager->update()
    }

    public function testMinorVersion()
    {
        $manager = new ChangelogManager($this->file);
    }

    public function testMajorVersion()
    {
        $manager = new ChangelogManager($this->file);
    }

    protected function tearDown()
    {
        exec('rm -rf '.$this->file);
    }

}
