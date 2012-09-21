<?php

namespace Liip\RD\Tests\Unit\Config;

use Liip\RD\Config\Handler;

class EasyHandler extends Handler {
    public function getDefaultConfig()
    {
        return array(
            'option1' => 'def1',
            'option2' => 'def2',
            'option3' => 'def3'
        );
    }
}

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Liip\RD\Config\Exception
     */
    public function testMergeWithoutAnAllSection()
    {
        $configHandler = new Handler();
        $configHandler->merge(array());
    }

    public function test3LevelsMerge()
    {
        $configHandler = new EasyHandler();
        $mergeConfig = $configHandler->merge(array(
            'all' => array(
                'option2' => 'all2',
                'option3' => 'all3',
                'option4' => 'all4'
            ),
            'dev' => array(
                'option3' => 'dev3',
                'option5' => 'dev5'
            )
        ), 'dev');
        $this->assertEquals(array(
            'option1' => 'def1',
            'option2' => 'all2',
            'option3' => 'dev3',
            'option4' => 'all4',
            'option5' => 'dev5'
        ), $mergeConfig);
    }

    /**
     * @expectedException \Liip\RD\Config\Exception
     * @expectedExceptionMessage Config error: key(s) [toto] are invalid, must be [vcs, prerequisites, pre-release-actions, version-generator, version-persister, post-release-actions]
     */
    public function testValidationWithExtraKeys(){
        $configHandler = new Handler();
        $config = $configHandler->merge(array('all'=>array('toto'=>'tata')));
        $configHandler->validateRootElements($config);
    }

    /**
     * @expectedException \Liip\RD\Config\Exception
     * @expectedExceptionMessage Config error: [version-generator] should be defined
     */
    public function testValidationWithMissingElement(){
        $configHandler = new Handler();
        $config = $configHandler->merge(array('all'=>array()));
        $configHandler->validateRootElements($config);
    }

    public function testNormalize(){
        $config = array(
            'vcs' => null,
            'prerequisites' => array(array('class'=>'\\DateTime', 'time'=>'now')),
            'pre-release-actions' => array(),
            'version-generator' => 'semantic',
            'version-persister' => '\\DateTime',
            'post-release-actions' => array()
        );
        $configHandler = new Handler();
        $config = $configHandler->normalize($config);
        $this->assertFalse(isset($config['vcs']));
        $this->assertEquals(array('class'=>'\\DateTime', 'options'=>array('time'=>'now')), $config['prerequisites'][0]);
        $this->assertEquals(array('class'=>'Liip\RD\Version\Generator\SemanticGenerator', 'options'=>array()), $config['version-generator']);
        $this->assertEquals(array('class'=>'\\DateTime', 'options'=>array()), $config['version-persister']);
    }

    /**
     * @dataProvider getDataForTestingGetClassAndOptions
     */
    public function testGetClassAndOptions($configKey, $rawConfig, $expectedClass, $expectedOptions)
    {
        $method = new \ReflectionMethod(
             'Liip\RD\Config\Handler', 'getClassAndOptions'
        );
        $method->setAccessible(TRUE);
        $this->assertEquals(
            array('class'=>$expectedClass, 'options'=>$expectedOptions),
            $method->invokeArgs(new Handler(), array($rawConfig, $configKey))
        );
    }
    public function getDataForTestingGetClassAndOptions()
    {
        return array(
            array('vcs', '\DateTime', '\DateTime', array()),
            array('vcs', 'git', 'Liip\RD\VCS\Git', array()),
            array('version-persister', 'vcs-tag', 'Liip\RD\Version\Persister\VcsTagPersister', array()),
            array('vcs', array('type'=>'git'), 'Liip\RD\VCS\Git', array()),
            array('vcs', array('type'=>'git', 'opt1'=>'val1'), 'Liip\RD\VCS\Git', array('opt1'=>'val1')),
            array('prerequisites_1', 'vcs-clean-check', 'Liip\RD\Prerequisite\VcsCleanCheck', array())
        );
    }

}
