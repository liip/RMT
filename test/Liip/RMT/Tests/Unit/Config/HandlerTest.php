<?php

namespace Liip\RMT\Tests\Unit\Config;

use Liip\RMT\Config\Handler;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Liip\RMT\Config\Exception
     * @expectedExceptionMessage Config error: key(s) [toto] are invalid, must be [vcs, prerequisites, pre-release-actions, version-generator, version-persister, post-release-actions, branch-specific]
     */
    public function testValidationWithExtraKeys()
    {
        $handler = new Handler(array('toto'=>'tata'));
        $handler->getBaseConfig();
    }

    /**
     * @expectedException \Liip\RMT\Config\Exception
     * @expectedExceptionMessage Config error: key(s) [toto] are invalid, must be [vcs, prerequisites, pre-release-actions, version-generator, version-persister, post-release-actions, branch-specific]
     */
    public function testValidationWithExtraKeysInBranchSpecific()
    {
        $handler = new Handler(array('branch-specific'=>array('dev'=>array('toto'=>'tata'))));
        $handler->getConfigForBranch('dev');
    }

    /**
     * @expectedException \Liip\RMT\Config\Exception
     * @expectedExceptionMessage Config error: [version-generator] should be defined
     */
    public function testValidationWithMissingElement()
    {
        $configHandler = new Handler(array('version-persister'=>'foo'));
        $configHandler->getBaseConfig();
    }

    public function testMerge()
    {
        $configHandler = new Handler(array(
            'version-persister' => 'foo',
            'version-generator' => 'bar',
            'branch-specific' => array(
                'dev' => array(
                    'version-generator' => 'foobar',
                )
            )
        ));
        $method = new \ReflectionMethod(
            'Liip\RMT\Config\Handler', 'mergeConfig'
        );
        $method->setAccessible(TRUE);
        $this->assertEquals($method->invokeArgs($configHandler, array()), array(
            'vcs' => null,
            'prerequisites' => array(),
            'pre-release-actions' => array(),
            'version-generator' => array(),
            'version-persister' => array (),
            'post-release-actions' => array(),
            'version-generator' => 'bar',
            'version-persister' => 'foo',
        ));
        $this->assertEquals($method->invokeArgs($configHandler, array('dev')), array(
            'vcs' => null,
            'prerequisites' => array(),
            'pre-release-actions' => array(),
            'version-generator' => array(),
            'version-persister' => array (),
            'post-release-actions' => array(),
            'version-generator' => 'foobar',
            'version-persister' => 'foo',
        ));
    }

    /**
     * @dataProvider getDataForTestingGetClassAndOptions
     */
    public function testGetClassAndOptions($configKey, $rawConfig, $expectedClass, $expectedOptions)
    {
        $configHandler = new Handler(array(
            'version-persister' => 'foo',
            'version-generator' => 'bar',
        ));
        $method = new \ReflectionMethod(
             'Liip\RMT\Config\Handler', 'getClassAndOptions'
        );
        $method->setAccessible(TRUE);
        $this->assertEquals(
            array('class'=>$expectedClass, 'options'=>$expectedOptions),
            $method->invokeArgs($configHandler, array($rawConfig, $configKey))
        );
    }
    public function getDataForTestingGetClassAndOptions()
    {
        return array(
            array('vcs', 'git', 'Liip\RMT\VCS\Git', array()),
            array('version-persister', 'vcs-tag', 'Liip\RMT\Version\Persister\VcsTagPersister', array()),
            array('vcs', array('name'=>'git'), 'Liip\RMT\VCS\Git', array()),
            array('vcs', array('name'=>'git', 'opt1'=>'val1'), 'Liip\RMT\VCS\Git', array('opt1'=>'val1')),
            array('prerequisites_1', 'vcs-clean-check', 'Liip\RMT\Prerequisite\VcsCleanCheck', array())
        );
    }

}
