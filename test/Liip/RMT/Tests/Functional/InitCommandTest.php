<?php

namespace Liip\RMT\Tests\Functional;


use Symfony\Component\Yaml\Yaml;

class InitCommandTest extends RMTFunctionalTestBase
{
    public function testInitConfig()
    {
        unlink('rmt.yml');
        $this->assertFileNotExists('rmt.yml');
//        $this->manualDebug();
        exec('./RMT init --vcs=git --generator=semantic-versioning --persister=vcs-tag -n');
        $this->assertFileExists('rmt.yml');
        $config = Yaml::parse(file_get_contents('rmt.yml'), true);
        $this->assertEquals('git', $config['vcs']);
        $this->assertEquals('vcs-tag', $config['version-persister']);
        $this->assertEquals('semantic', $config['version-generator']);
    }
}

