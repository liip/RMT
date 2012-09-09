<?php

namespace Liip\RD\Tests\Functional;


class GitTest extends RDFunctionalTestBase
{

    public function testSimple(){
        $this->initGit();
        exec('git tag 1');
        exec('git tag 3');
        exec('git tag toto');
        $this->setJsonConfig(<<<JSON
{
    "default": {
        "vcs": "git",
        "version_generator": "simple",
        "version_persister": "vcs-tag"
    }
}
JSON
);
        exec('./RD release');
        exec('git tag', $tags);
        $this->assertEquals(array('1','3', '4', 'toto'), $tags);
    }


    public function testSemantic()
    {
        $this->initGit();
        exec('git tag 2.1.19');
        $this->setJsonConfig(<<<JSON
{
    "default": {
        "vcs": "git",
        "version_generator": "semantic",
        "version_persister": "vcs-tag"
    }
}
JSON
        );
        exec('./RD release --type=minor');
        exec('git tag', $tags);
        $this->assertEquals(array('2.1.19', '2.2.0'), $tags);
    }

    protected function initGit()
    {
        exec('git init');
        exec('git add *');
        exec('git commit -m "First commit"');
    }

}
