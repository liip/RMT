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

use Liip\RMT\Changelog\ChangelogManager;
use Symfony\Component\Yaml\Yaml;

class RMTFunctionalTestBase extends ForwardCompatibilityTestCase
{
    protected $tempDir;
    protected const DEFAULT_FUNCTIONAL_BRANCH = 'master';

    protected function setUp(): void
    {
        // Create a temp folder
        $this->tempDir = tempnam(sys_get_temp_dir(), '');
        if (file_exists($this->tempDir)) {
            unlink($this->tempDir);
        }
        mkdir($this->tempDir);
        chdir($this->tempDir);

        // Create the executable task inside
        $rmtDir = realpath(__DIR__ . '/../../../../../');
        exec("php $rmtDir/command.php init --configonly=n --generator=basic-increment --persister=vcs-tag --vcs=git --main-branch=" . self::DEFAULT_FUNCTIONAL_BRANCH);
    }

    protected function createConfig($generator, $persister, $otherConfig = array()): void
    {
        $allConfig = array_merge($otherConfig, array(
            'version-persister' => $persister,
            'version-generator' => $generator,
        ));
        file_put_contents('.rmt.yml', Yaml::dump($allConfig));
    }

    protected function createChangelog($format): void
    {
        $file = $this->tempDir . '/CHANGELOG';
        $manager = new ChangelogManager($file, $format);
        $manager->update(
            $format === 'semantic' ? '0.0.1' : '1',
            'First release',
            $format === 'semantic' ? array('type' => 'patch') : null
        );
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . $this->tempDir);
    }

    protected function initGit(): void
    {
        exec('git config --global init.defaultBranch ' . self::DEFAULT_FUNCTIONAL_BRANCH);
        exec('git init');
        exec('git add .');
        exec('git commit -m "First commit"');
    }

    protected function manualDebug(): void
    {
        echo "\n\nMANUAL DEBUG Go to:\n > cd " . $this->tempDir . "\n\n";
        exit();
    }
}
