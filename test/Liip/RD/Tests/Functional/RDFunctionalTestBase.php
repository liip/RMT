<?php

namespace Liip\RD\Tests\Functional;

class RDFunctionalTestBase extends \PHPUnit_Framework_TestCase
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
        $rdDir = realpath(__DIR__.'/../../../../../');
        file_put_contents('RD', <<<EOF
#!/usr/bin/env php
<?php define('RD_CONFIG_DIR', __DIR__); ?>
<?php require '$rdDir/command.php'; ?>
EOF
        );
        exec('chmod +x RD');
    }

    protected function createJsonConfig($generator, $persister, $otherConfig=array(), $envSpecificConfig=array()) {
        $allConfig = array_merge($otherConfig, array(
            'version-persister'=>$persister,
            'version-generator'=>$generator
        ));
        $envSpecificConfig['all'] = $allConfig;
        file_put_contents('rd.json', json_encode($envSpecificConfig));
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
