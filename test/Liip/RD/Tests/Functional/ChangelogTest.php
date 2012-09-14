<?php

namespace Liip\RD\Tests\Functional;


class ChangelogTest extends RDFunctionalTestBase
{
    public function testSimple(){
        file_put_contents($this->tempDir.'/CHANGELOG', <<<CHANGELOG

Version 1 - Changelog init
    06/09/2012 08:34  1  First version
CHANGELOG
);
        $this->setJsonConfig(<<<JSON
{
    "all": {
        "version-generator": "simple",
        "version-persister": "changelog"
    }
}
JSON
        );
        exec('./RD release --comment="test"');
        $changelogLines = file($this->tempDir.'/CHANGELOG', FILE_IGNORE_NEW_LINES);
        $this->assertRegExp('/2\s\stest/', $changelogLines[2]);
    }
}
