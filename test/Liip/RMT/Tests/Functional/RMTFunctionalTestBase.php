<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Functional;

use Symfony\Component\Yaml\Yaml;

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
        exec("php $rmtDir/command.php init --configonly=n --generator=basic-increment --persister=vcs-tag --vcs=git");
    }

    protected function createConfig($generator, $persister, $otherConfig=array()) {
        $allConfig = array_merge($otherConfig, array(
            'version-persister'=>$persister,
            'version-generator'=>$generator
        ));
        file_put_contents('.rmt.yml', Yaml::dump($allConfig));
    }

    protected function tearDown()
    {
        exec('rm -rf '.$this->tempDir);
    }

    protected function initGit()
    {
        exec('git init');
        exec('git add .');
        exec('git commit -m "First commit"');
    }

    protected function  manualDebug()
    {
        echo "\n\nMANUAL DEBUG Go to:\n > cd ".$this->tempDir."\n\n"; exit();
    }

}
