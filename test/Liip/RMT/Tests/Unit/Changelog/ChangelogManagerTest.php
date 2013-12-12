<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Unit\Changelog;

use Liip\RMT\Changelog\ChangelogManager;

class ChangelogManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $dir;

    public function testAutoFileCreationWhenNoFound()
    {
        $file = tempnam(sys_get_temp_dir(),'CHANGELOG');
        new ChangelogManager($file, 'semantic');
        $this->assertFileExists($file);
        unlink($file);
    }

    /**
     * @expectedException \Liip\RMT\Exception
     */
    public function testExceptionWhenNotAbleToCreate()
    {
        $this->dir = sys_get_temp_dir().'/'.md5(time());
        mkdir($this->dir);
        new ChangelogManager($this->dir, 'semantic');
    }

    protected function tearDown()
    {
        exec('rm -rf '.$this->dir);
    }

}
