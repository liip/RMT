<?php

namespace Liip\RD\Tests\Functional;


class InitCommandTest extends RDFunctionalTestBase
{
    public function testInit()
    {
        $this->assertFileNotExists('rd.json');
//        $this->manualDebug();
        exec('RD init --vcs=git --generator=semantic-versioning --persister=vcs-tag -n');
        $this->assertFileExists('rd.json');
        $config = json_decode(file_get_contents('rd.json'), true);
        $this->assertEquals('git', $config['vcs']);
        $this->assertEquals('vcs-tag', $config['version-persister']);
        $this->markTestSkipped('Issue #10 identified in github');
        $this->assertEquals('semantic', $config['version-generator']);
    }
}

