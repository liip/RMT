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
//        $this->manualDebug();
        exec('./RMT init --vcs=git --generator=semantic-versioning --persister=vcs-tag -n');
        $this->assertFileExists($configFile);
        $config = Yaml::parse(file_get_contents($configFile), true);
        $masterConfig = $config["branch-specific"]["master"];

        $this->assertEquals('git', $config['vcs']);

        $this->assertEquals('simple', $config['version-generator']);
        $this->assertEquals('semantic', $masterConfig['version-generator']);

        $this->assertEquals(array("vcs-tag"=>array("tag-prefix"=>"{branch-name}_")), $config['version-persister']);
        $this->assertEquals(array("vcs-tag"=>array("tag-prefix"=>"")), $masterConfig['version-persister']);
    }
}

