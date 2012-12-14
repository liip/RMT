<?php

namespace Liip\RMT\Tests\Unit\Changelog;

use Liip\RMT\Changelog\ChangelogManager;

class ChangelogManagerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->file = tempnam(sys_get_temp_dir(),'CHANGELOG');
        touch($this->file);
    }

    /**
     * @expectedException \Liip\RMT\Exception
     */
    public function testExceptionWhenNoFileFound()
    {
        unlink($this->file);
        new ChangelogManager($this->file, 'semantic');
    }

    protected function tearDown()
    {
        exec('rm -rf '.$this->file);
    }

}
