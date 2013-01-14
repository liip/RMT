<?php

namespace Liip\RMT\Tests\Functional;


class InitCommandTest extends RMTFunctionalTestBase
{
    public function testInitConfig()
    {
        unlink('rmt.json');
        $this->assertFileNotExists('rmt.json');
//        $this->manualDebug();
        exec('./RMT init --vcs=git --generator=semantic-versioning --persister=vcs-tag -n');
        $this->assertFileExists('rmt.json');
        $config = json_decode(file_get_contents('rmt.json'), true);
        $this->assertEquals('git', $config['vcs']);
        $this->assertEquals('vcs-tag', $config['version-persister']);
        $this->assertEquals('semantic', $config['version-generator']);
    }
}

