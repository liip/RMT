<?php

namespace Liip\RD\Tests\Unit;

use Liip\RD\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConfigMustAlwaysHaveADefaultSection()
    {
        new Config(array());
    }

    public function testSetAndGetFromASpecificEnv()
    {
        $config = new Config(array(
            'default' => array('vcs' => 'git'),
            'dev' => array('vcs' => 'svn')
        ));
        $config->setEnv('default'); // Can I remove that? Please...
        $this->assertEquals('git', $config->getVCS());
        $config->setEnv('dev');
        $this->assertEquals('svn', $config->getVCS());
    }

    public function testGetVersionPersister()
    {
        $config = new Config(array(
            'default' => array('version_persister' => array('type' => 'git'))
        ));
        $config->setEnv('default'); // Can I remove that? Please...
        $this->assertEquals('git', $config->getVersionPersister(), '->getVersionPersister() with full detail');

        $config = new Config(array(
            'default' => array('version_persister' => 'git')
        ));
        $config->setEnv('default'); // Can I remove that? Please...
        $this->assertEquals('git', $config->getVersionPersister(), '->getVersionPersister() with short syntax');
    }

    public function testGetVersionPersisterOptions()
    {
        $config = new Config(array(
            'default' => array('version_persister' => 'git')
        ));
        $config->setEnv('default'); // Can I remove that? Please...
        $this->assertEquals(array(), $config->getVersionPersisterOptions(), '->getVersionPersisterOptions() when using short syntax');

        $config = new Config(array(
            'default' => array('version_persister' => array('type' => 'git'))
        ));
        $config->setEnv('default'); // Can I remove that? Please...
        $this->assertEquals(array(), $config->getVersionPersisterOptions(), '->getVersionPersisterOptions() when using long syntax but no options');


        $config = new Config(array(
            'default' => array('version_persister' => array('type' => 'git', 'option1' => 'toto'))
        ));
        $config->setEnv('default'); // Can I remove that? Please...
        $this->assertEquals(array('option1'=>'toto'), $config->getVersionPersisterOptions(), '->getVersionPersisterOptions() return options');
    }

}
