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

use Liip\RMT\Config\Exception;
use Liip\RMT\Config\Handler;
use PHPUnit\Framework\TestCase;
use Liip\RMT\Version\Persister\VcsTagPersister;
use Liip\RMT\VCS\Git;
use ReflectionMethod;

class HandlerTest extends TestCase
{
    public function testValidationWithExtraKeys(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Config error: key(s) [toto] are invalid');

        $handler = new Handler(['toto' => 'tata']);
        $handler->getBaseConfig();
    }

    public function testValidationWithExtraKeysInBranchSpecific(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Config error: key(s) [toto] are invalid');

        $handler = new Handler(['branch-specific' => ['dev' => ['toto' => 'tata']]]);
        $handler->getConfigForBranch('dev');
    }

    public function testValidationWithMissingElement(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Config error: [version-generator] should be defined');

        $configHandler = new Handler(['version-persister' => 'foo']);
        $configHandler->getBaseConfig();
    }

    /**
     * @dataProvider getDataForGetBaseConfig
     */
    public function testGetBaseConfig(array $rawConfig, string $expectedGenerator): void
    {
        $handler = new Handler($rawConfig);
        $config = $handler->getBaseConfig();

        self::assertEquals($config['version-generator']['class'], $expectedGenerator);
    }
    public function getDataForGetBaseConfig(): array
    {
        return [
            // Legacy format
            [
                [
                    'version-persister' => 'foo',
                    'version-generator' => 'foo',
                ],
                'Liip\RMT\Version\Generator\FooGenerator',
            ],
            // New format (see: https://github.com/liip/RMT/issues/56)
            [
                [
                    '_default' => [
                        'version-persister' => 'foo',
                        'version-generator' => 'foo',
                    ],
                ],
                'Liip\RMT\Version\Generator\FooGenerator',
            ],
        ];
    }

    /**
     * @dataProvider getDataForGetBranchConfig
     */
    public function testGetBranchConfig(array $rawConfig, string $branch, string $expected): void
    {
        $handler = new Handler($rawConfig);
        $config = $handler->getConfigForBranch($branch);
        self::assertEquals($config['version-generator']['class'], $expected);
    }

    public function getDataForGetBranchConfig(): array
    {
        return [
            // Legacy format
            [
                [
                    'version-persister' => 'foo',
                    'version-generator' => 'foo',
                    'branch-specific' => [
                        'dev' => ['version-generator' => 'bar'],
                    ],
                ],
                'dev',
                'Liip\RMT\Version\Generator\BarGenerator',
            ],
            // New format (see: https://github.com/liip/RMT/issues/56)
            [
                [
                    '_default' => [
                        'version-persister' => 'foo',
                        'version-generator' => 'foo',
                    ],
                    'dev' => [
                        'version-generator' => 'bar',
                    ],
                ],
                'dev',
                'Liip\RMT\Version\Generator\BarGenerator',
            ],
        ];
    }

    public function testMerge(): void
    {
        $configHandler = new Handler([
            'version-persister' => 'foo',
            'version-generator' => 'bar',
            'branch-specific' => [
                'dev' => [
                    'version-generator' => 'foobar',
                ],
            ],
        ]);

        $method = new ReflectionMethod(Handler::class, 'mergeConfig');
        $method->setAccessible(true);

        self::assertEquals([
            'vcs' => null,
            'prerequisites' => [],
            'pre-release-actions' => [],
            'post-release-actions' => [],
            'version-generator' => 'bar',
            'version-persister' => 'foo',
        ], $method->invokeArgs($configHandler, []));

        self::assertEquals([
            'vcs' => null,
            'prerequisites' => [],
            'pre-release-actions' => [],
            'post-release-actions' => [],
            'version-generator' => 'foobar',
            'version-persister' => 'foo',
        ], $method->invokeArgs($configHandler, ['dev']));
    }

    public function testMergeOptions(): void
    {
        $configHandler = new Handler([
            'version-persister' => 'foo',
            'version-generator' => ['name' => 'bar', 'opt1' => 'val1'],
            'branch-specific' => [
                'dev' => [
                    'version-generator' => ['opt1' => 'val2'],
                ],
            ],
        ]);

        $method = new ReflectionMethod(Handler::class, 'mergeConfig');
        $method->setAccessible(true);

        self::assertEquals([
            'vcs' => null,
            'prerequisites' => [],
            'pre-release-actions' => [],
            'post-release-actions' => [],
            'version-generator' => ['name' => 'bar', 'opt1' => 'val1'],
            'version-persister' => 'foo',
        ], $method->invokeArgs($configHandler, []));

        self::assertEquals([
            'vcs' => null,
            'prerequisites' => [],
            'pre-release-actions' => [],
            'post-release-actions' => [],
            'version-generator' => ['name' => 'bar', 'opt1' => 'val2'],
            'version-persister' => 'foo',
        ], $method->invokeArgs($configHandler, ['dev']));
    }

    /**
     * @dataProvider getDataForTestingGetClassAndOptions
     */
    public function testGetClassAndOptions(string $configKey, $rawConfig, string $expectedClass, array $expectedOptions): void
    {
        $configHandler = new Handler([
            'version-persister' => 'foo',
            'version-generator' => 'bar',
        ]);

        $method = new ReflectionMethod(Handler::class, 'getClassAndOptions');
        $method->setAccessible(true);

        self::assertEquals(
            ['class' => $expectedClass, 'options' => $expectedOptions],
            $method->invokeArgs($configHandler, [$rawConfig, $configKey])
        );
    }

    public function getDataForTestingGetClassAndOptions(): array
    {
        return [
            ['version-persister', 'vcs-tag', VcsTagPersister::class, []],
            // vcs: git
            ['vcs', 'git', Git::class, []],
            // vcs:
            //   git: ~
            ['vcs', ['git' => null], Git::class, []],
            // vcs:
            //   git:
            //     opt1: val1
            ['vcs', ['git' => ['opt1' => 'val1']], Git::class, ['opt1' => 'val1']],
            // vcs: {name: git}
            ['vcs', ['name' => 'git'], Git::class, []],
            // vcs: {name: git, opt1: val1}
            ['vcs', ['name' => 'git', 'opt1' => 'val1'], Git::class, ['opt1' => 'val1']],
            ['prerequisites_1', 'vcs-clean-check', 'Liip\RMT\Prerequisite\VcsCleanCheck', []],
        ];
    }
}
