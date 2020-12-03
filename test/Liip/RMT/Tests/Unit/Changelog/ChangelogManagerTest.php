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
use Liip\RMT\Exception;
use PHPUnit\Framework\TestCase;

class ChangelogManagerTest extends TestCase
{
    protected $dir;

    public function testAutoFileCreationWhenNoFound(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'CHANGELOG');
        new ChangelogManager($file, 'semantic');
        self::assertFileExists($file);
        unlink($file);
    }

    public function testExceptionWhenNotAbleToCreate(): void
    {
        $this->expectException(Exception::class);

        $this->dir = sys_get_temp_dir() . '/' . md5(time());
        mkdir($this->dir);
        new ChangelogManager($this->dir, 'semantic');
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . $this->dir);
    }
}
