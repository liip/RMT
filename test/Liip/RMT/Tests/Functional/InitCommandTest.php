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
    public function testInitConfig()
    {
        $configFile = '.rmt.yml';
        unlink($configFile);
        $this->assertFileNotExists($configFile);
        exec('./RMT init --configonly=n --vcs=git --generator=semantic-versioning --persister=vcs-tag -n');

//        $this->manualDebug();

        $this->assertFileExists($configFile);
        $config = Yaml::parse(file_get_contents($configFile), true);

        $defaultConfig = $config['_default'];
        $masterConfig = $config['master'];

        $this->assertEquals('git', $defaultConfig['vcs']);

        $this->assertEquals('simple', $defaultConfig['version-generator']);
        $this->assertEquals('semantic', $masterConfig['version-generator']);

        $this->assertEquals(array('vcs-tag' => array('tag-prefix' => '{branch-name}_')), $defaultConfig['version-persister']);
        $this->assertEquals(array('vcs-tag' => array('tag-prefix' => '')), $masterConfig['version-persister']);
    }
}
