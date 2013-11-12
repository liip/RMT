<?php

namespace Liip\RMT\Tests\Functional;


class ChangelogDumpCommitsTest extends RMTFunctionalTestBase
{
    public function testDump()
    {
        $this->createConfig("semantic", "vcs-tag", array(
            "vcs" => "git",
            "pre-release-actions" => array(
                "changelog-update" => array(
                    "format" => "semantic",
                    "dump-commits" => true
                ),
                "vcs-commit" => null
            )
        ));
        $this->initGit();

        // First release must contain as message explaining why there is no commit dump
        exec('./RMT release -n --confirm-first --comment="First release"', $output);
        $output = implode("\n", $output);
        $this->assertContains('No commits dumped as this is the first release', $output);

        // Next release must update the CHANGELOG
        exec('echo "text" > new-file && git add -A && git commit -m "Second commit"');
        exec('echo "text2" >> new-file && git commit -am "Third commit"');
        exec('./RMT release -n --comment="Second release"', $output);
        $changelog = file_get_contents($this->tempDir.'/CHANGELOG');
        $this->assertContains('Second commit', $changelog);
        $this->assertContains('Third commit', $changelog);
    }

}

