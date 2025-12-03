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

class CommandActionTest extends RMTFunctionalTestBase
{
    public function testCommand(): void
    {
        $this->createChangelog('simple');
        $this->createConfig('simple', 'changelog', array(
            'pre-release-actions' => array(
                'command' => array('cmd' => 'echo "hello world"'),
            ),
        ));
        exec('./RMT release -n --no-ansi --comment="test"', $output);
        $output = implode("\n", $output);
//        $this->manualDebug();
        self::assertStringContainsString('Command Action : echo "hello world"', $output);
    }

    public function testVersionsOutputOnPostRelease()
    {
        $this->createChangelog('simple');
        $this->createConfig('simple', 'changelog', array(
            'post-release-actions' => array(
                'command' => array('cmd' => 'echo %version% %new_version%'),
            ),
        ));

        exec('./RMT release -n --no-ansi --comment="test"', $output);
        $output = implode("\n", $output);
        $this->assertStringContainsString('Command Action : echo 1 2', $output);
    }
}
