<?php

namespace Liip\RD\Tests\Functional;

use Liip\RD\ReleaseCommand;

class PrerequisitesTest extends RDFunctionalTestBase
{

    public function testDisplayLastChange()
    {
        $this->createJsonConfig('simple', 'vcs-tag', array(
            'prerequisites' => array('display-last-changes'),
            'vcs' => 'git'
        ));
        $this->initGit();
        exec('git tag 1');
        exec('echo "foo" > fileFoo');
        exec('git add fileFoo');
        exec('git commit -m "Add a simple file"');
        exec('git mv fileFoo fileBar');
        exec('git commit -m "Rename foo to bar"');


        ReleaseCommand::$projectRoot = $this->tempDir;
        $command = new ReleaseCommand();
        $test = new \Symfony\Component\Console\Tester\CommandTester($command);
        $test->execute(array());
        $consoleOutput = $test->getDisplay();
        $this->assertNotContains("First commit", $consoleOutput);
        $this->assertContains("Add a simple file", $consoleOutput);
        $this->assertContains("Rename foo to bar", $consoleOutput);
    }

    public function testWorkingCopyCheck()
    {
        $this->markTestSkipped("Not ready yet");
        $this->createJsonConfig('simple', 'vcs-tag', array(
            'prerequisites' => array('working-copy-check'),
            'vcs' => 'git'
        ));
        $this->initGit();
        exec('touch toto');
        exec('./RD release');
        exec('git tag', $tags);
//        $this->manualDebug();
        $this->assertEquals(array('1','3', '4', 'toto'), $tags);
    }


}
