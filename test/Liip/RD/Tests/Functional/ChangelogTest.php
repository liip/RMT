<?php

namespace Liip\RD\Tests\Functional;


class ChangelogTest extends RDFunctionalTestBase
{
    public function testSimple(){
        $this->createChangelog('1');
        $this->createJsonConfig("simple", "changelog");

        $this->executeTest(null, 'test', '2');
    }

    public function testSemantic(){
        $this->createChangelog('1.0.0');
        $this->createJsonConfig("semantic", "changelog");

        $this->executeTest('major', 'test_major', '2.0.0');
        $this->executeTest('patch', 'test_patch', '2.0.1');
        $this->executeTest('minor', 'test_minor', '2.1.0');
        $this->executeTest('major', 'test_major_reset', '3.0.0');
    }

    /**
     * Create changelog file
     * @param String initial version (ie: 2.0.0)
     */
    protected function createChangelog($initialVersion)
    {
        file_put_contents($this->tempDir.'/CHANGELOG', <<<CHANGELOG

Version 1 - Changelog init
    06/09/2012 08:34  $initialVersion  First version
CHANGELOG
);
    }

    /**
     * Execute changelog test
     * @param String [major/minor/patch]
     * @param String comment
     * @param String expected version number (ie 2.0.0)
     */
    protected function executeTest($semanticType, $comment, $expected)
    {
        if (is_null($semanticType)) {
            exec('./RD release --comment="'.$comment.'"');
        } else {
            exec('./RD release --type='.$semanticType.' --comment="'.$comment.'"');
        }
        $changelogLines = file($this->tempDir.'/CHANGELOG', FILE_IGNORE_NEW_LINES);
        //$this->manualDebug();
        $this->assertRegExp('/'.$expected.'\s\s'.$comment.'/', $changelogLines[2]);
    }
}

