<?php

namespace Liip\RD\Tests\Functional;


class PrerequisitesTest extends RDFunctionalTestBase
{

    public function testPrerequisitesFail()
    {
        $this->markTestSkipped("Not working yet");
        $this->setJsonConfig(<<<JSON
{
    "default": {
        "prerequisites": ["check-working-copy", "display-last-changes"],
        "vcs": "git",
        "version_generator": "simple",
        "version_persister": "vcs-tag"
    }
}
JSON
);
        $this->initGit();
        exec('touch toto');
        exec('./RD release');
        exec('git tag', $tags);
        $this->manualDebug();
        $this->assertEquals(array('1','3', '4', 'toto'), $tags);
    }

}
