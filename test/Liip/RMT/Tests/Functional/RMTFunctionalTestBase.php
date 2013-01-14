<?php

namespace Liip\RMT\Tests\Functional;

class RMTFunctionalTestBase extends \PHPUnit_Framework_TestCase
{
    protected $tempDir;

    protected function setUp() {

        // Create a temp folder
        $this->tempDir = tempnam(sys_get_temp_dir(),'');
        if (file_exists($this->tempDir)) {
            unlink($this->tempDir);
        }
        mkdir($this->tempDir);
        chdir($this->tempDir);

        // Create the executable task inside
        $rmtDir = realpath(__DIR__.'/../../../../../');
        exec("php $rmtDir/command.php init --generator=basic-increment --persister=vcs-tag --vcs=git");
    }

    protected function createJsonConfig($generator, $persister, $otherConfig=array()) {
        $allConfig = array_merge($otherConfig, array(
            'version-persister'=>$persister,
            'version-generator'=>$generator
        ));
        file_put_contents('rmt.json', json_encode($allConfig));
    }

    protected function tearDown()
    {
        exec('rm -rf '.$this->tempDir);
    }

    protected function initGit()
    {
        exec('git init');
        exec('git add *');
        exec('git commit -m "First commit"');
    }

    protected function  manualDebug()
    {
        echo "\n\nMANUAL DEBUG Go to:\n > cd ".$this->tempDir."\n\n"; exit();
    }

}
