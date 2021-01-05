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

class ExternalTasksTest extends RMTFunctionalTestBase
{
    public function testInvalidScript(): void
    {
        $scriptName = 'invalid-script-name.php';
        $this->createConfig('simple', 'git', array('pre-release-actions' => array($scriptName)));
        exec('./RMT release -n', $output);
        $output = implode("\n", $output);
//        $this->manualDebug();
        self::assertStringContainsString('Impossible to open', $output);
        self::assertStringContainsString($scriptName, $output);
    }

    public function testExternalTouch(): void
    {
        $this->initGit();
        file_put_contents('touch-file1.php', '<?php touch("file1");');
        $this->createConfig('simple', 'git', array(
            'pre-release-actions' => array('touch-file1.php'),
        ));
        exec('./RMT release -n');
        exec('ls', $files);
        self::assertContains('file1', $files, 'file1 in present in ['.implode(', ', $files).']');
    }
}
