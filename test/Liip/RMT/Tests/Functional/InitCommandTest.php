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

use Symfony\Component\Yaml\Yaml;

class InitCommandTest extends RMTFunctionalTestBase
{
    public function testInitConfig(): void
    {
        $configFile = '.rmt.yml';
        unlink($configFile);
        self::assertFileDoesNotExist($configFile);
        exec('./RMT init --configonly=n --vcs=git --main-branch=main --generator=semantic-versioning --persister=vcs-tag -n');

        self::assertFileExists($configFile);
        $config = Yaml::parse(file_get_contents($configFile), true);

        $defaultConfig = $config['_default'];
        $mainConfig = $config['main'];

        self::assertEquals('git', $defaultConfig['vcs']['name']);
        self::assertEquals('main', $defaultConfig['vcs']['main-branch']);

        self::assertEquals('simple', $defaultConfig['version-generator']);
        self::assertEquals('semantic', $mainConfig['version-generator']);

        self::assertEquals(array('vcs-tag' => array('tag-prefix' => '{branch-name}_')), $defaultConfig['version-persister']);
        self::assertEquals(array('vcs-tag' => array('tag-prefix' => '')), $mainConfig['version-persister']);
    }
}
