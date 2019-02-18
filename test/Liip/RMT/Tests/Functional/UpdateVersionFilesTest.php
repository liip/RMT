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

class UpdateVersionFilesTest extends RMTFunctionalTestBase
{
    public function testTwoUpdate()
    {
        $ymlBefore = <<<YML
my-project:
   version: 5
YML;
        $iniBefore = <<<INI
stable-version: 5
dynamic-version: 5
INI;
        $ymlAfter = <<<YML
my-project:
   version: 6
YML;
        $iniAfter = <<<INI
stable-version: 5
dynamic-version: 6
INI;
        $this->createChangelog('simple');
        $this->createConfig('simple', 'changelog', ['pre-release-actions' => [
            'update-version-files' => [
                ['config.yml'],
                ['app.ini', "const VERSION = '%version%';"]
        ]]]);

        file_put_contents('config.yml', $ymlBefore);
        file_put_contents('app.ini', $iniBefore);
        $this->manualDebug();
        exec('./RMT release -n', $output);
        $this->assertEquals($ymlAfter, file_get_contents('config.yml'));
        $this->assertEquals($iniAfter, file_get_contents('app.ini'));
    }

}
