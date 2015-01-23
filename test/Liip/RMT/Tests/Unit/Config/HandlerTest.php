<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Unit\Config;

use Liip\RMT\Config\Handler;

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Liip\RMT\Config\Exception
     * @expectedExceptionMessage Config error: key(s) [toto] are invalid
     */
    public function testValidationWithExtraKeys()
    {
        $handler = new Handler(array('toto' => 'tata'));
        $handler->getBaseConfig();
    }

    /**
     * @expectedException \Liip\RMT\Config\Exception
     * @expectedExceptionMessage Config error: key(s) [toto] are invalid
     */
    public function testValidationWithExtraKeysInBranchSpecific()
    {
        $handler = new Handler(array('branch-specific' => array('dev' => array('toto' => 'tata'))));
        $handler->getConfigForBranch('dev');
    }

    /**
     * @expectedException \Liip\RMT\Config\Exception
     * @expectedExceptionMessage Config error: [version-generator] should be defined
     */
    public function testValidationWithMissingElement()
    {
        $configHandler = new Handler(array('version-persister' => 'foo'));
        $configHandler->getBaseConfig();
    }

    /**
     * @dataProvider getDataForGetBaseConfig
     */
    public function testGetBaseConfig($rawConfig, $expectedGenerator)
    {
        $handler = new Handler($rawConfig);
        $config = $handler->getBaseConfig();
        $this->assertEquals($config['version-generator']['class'], $expectedGenerator);
    }
    public function getDataForGetBaseConfig()
    {
        return array(
            // Legacy format
            array(
                array(
                    'version-persister' => 'foo',
                    'version-generator' => 'foo'
                ),
                'Liip\RMT\Version\Generator\FooGenerator'
            ),
            // New format (see: https://github.com/liip/RMT/issues/56)
            array(
                array(
                    '_default' => array(
                        'version-persister' => 'foo',
                        'version-generator' => 'foo'
                    )
                ),
                'Liip\RMT\Version\Generator\FooGenerator'
            ),
        );
    }

    /**
     * @dataProvider getDataForGetBranchConfig
     */
    public function testGetBranchConfig($rawConfig, $branch, $expected)
    {
        $handler = new Handler($rawConfig);
        $config = $handler->getConfigForBranch($branch);
        $this->assertEquals($config['version-generator']['class'], $expected);
    }

    public function getDataForGetBranchConfig()
    {
        return array(
            // Legacy format
            array(
                array(
                    'version-persister' => 'foo',
                    'version-generator' => 'foo',
                    'branch-specific' => array(
                        'dev' => array('version-generator' => 'bar')
                    )
                ),
                'dev',
                'Liip\RMT\Version\Generator\BarGenerator'
            ),
            // New format (see: https://github.com/liip/RMT/issues/56)
            array(
                array(
                    '_default' => array(
                        'version-persister' => 'foo',
                        'version-generator' => 'foo'
                    ),
                    'dev' => array(
                        'version-generator' => 'bar'
                    )
                ),
                'dev',
                'Liip\RMT\Version\Generator\BarGenerator'
            )
        );
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

        $method = new \ReflectionMethod('Liip\RMT\Config\Handler', 'mergeConfig');
        $method->setAccessible(true);

        $this->assertEquals($method->invokeArgs($configHandler, array()), array(
            'bootstrap' => null,
            'vcs' => null,
            'prerequisites' => array(),
            'pre-release-actions' => array(),
            'post-release-actions' => array(),
            'version-generator' => 'bar',
            'version-persister' => 'foo',
        ));
        $this->assertEquals($method->invokeArgs($configHandler, array('dev')), array(
            'bootstrap' => null,
            'vcs' => null,
            'prerequisites' => array(),
            'pre-release-actions' => array(),
            'post-release-actions' => array(),
            'version-generator' => 'foobar',
            'version-persister' => 'foo',
        ));
    }

    public function testMergeOptions()
    {
        $configHandler = new Handler(array(
            'version-persister' => 'foo',
            'version-generator' => array('name' => 'bar', 'opt1' => 'val1'),
            'branch-specific' => array(
                'dev' => array(
                    'version-generator' => array('opt1' => 'val2')
                )
            )
        ));

        $method = new \ReflectionMethod('Liip\RMT\Config\Handler', 'mergeConfig');
        $method->setAccessible(true);

        $this->assertEquals($method->invokeArgs($configHandler, array()), array(
            'bootstrap' => null,
            'vcs' => null,
            'prerequisites' => array(),
            'pre-release-actions' => array(),
            'post-release-actions' => array(),
            'version-generator' => array('name' => 'bar', 'opt1' => 'val1'),
            'version-persister' => 'foo',
        ));
        $this->assertEquals($method->invokeArgs($configHandler, array('dev')), array(
            'bootstrap' => null,
            'vcs' => null,
            'prerequisites' => array(),
            'pre-release-actions' => array(),
            'post-release-actions' => array(),
            'version-generator' => array('name' => 'bar', 'opt1' => 'val2'),
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

        $method = new \ReflectionMethod('Liip\RMT\Config\Handler', 'getClassAndOptions');
        $method->setAccessible(true);

        $this->assertEquals(
            array('class' => $expectedClass, 'options' => $expectedOptions),
            $method->invokeArgs($configHandler, array($rawConfig, $configKey))
        );
    }

    public function getDataForTestingGetClassAndOptions()
    {
        return array(
            array('version-persister', 'vcs-tag', 'Liip\RMT\Version\Persister\VcsTagPersister', array()),
            // vcs: git
            array('vcs', 'git', 'Liip\RMT\VCS\Git', array()),
            // vcs:
            //   git: ~
            array('vcs', array('git' => null), 'Liip\RMT\VCS\Git', array()),
            // vcs:
            //   git:
            //     opt1: val1
            array('vcs', array('git' => array('opt1' => 'val1')), 'Liip\RMT\VCS\Git', array('opt1' => 'val1')),
            // vcs: {name: git}
            array('vcs', array('name' => 'git'), 'Liip\RMT\VCS\Git', array()),
            // vcs: {name: git, opt1: val1}
            array('vcs', array('name' => 'git', 'opt1' => 'val1'), 'Liip\RMT\VCS\Git', array('opt1' => 'val1')),
            array('prerequisites_1', 'vcs-clean-check', 'Liip\RMT\Prerequisite\VcsCleanCheck', array()),
            // vcs: Foo\Bar
            array('vcs', 'Foo\Bar', 'Foo\Bar', array()),
            // vcs: Foo
            array('vcs', 'Foo', 'Liip\RMT\VCS\Foo', array()),
            // vcs: \Foo
            array('vcs', '\Foo', '\Foo', array()),
            // pre-release-actions: Foo\Bar
            array('pre-release-actions', 'Foo\Bar', 'Foo\Bar', array()),
            // pre-release-actions: Foo
            array('pre-release-actions', 'Foo', 'Liip\RMT\Action\FooAction', array()),
            // pre-release-actions: \Foo
            array('pre-release-actions', '\Foo', '\Foo', array())
        );
    }
}
