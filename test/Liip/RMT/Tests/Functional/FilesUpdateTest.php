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

class FilesUpdateTest extends RMTFunctionalTestBase
{
    public function testTwoUpdate()
    {
        $ymlBefore = <<<YML
my-project:
   version: 1
YML;
        $iniBefore = <<<INI
stable-version: 1
dynamic-version: 1
INI;
        $ymlAfter = <<<YML
my-project:
   version: 2
YML;
        $iniAfter = <<<INI
stable-version: 1
dynamic-version: 2
INI;
        $this->createChangelog('simple');
        $this->createConfig('simple', 'changelog', ['pre-release-actions' => [
            'files-update' => [
                ['config.yml'],
                ['app.ini', "dynamic-version: %version%"]
            ]]]);

        file_put_contents('config.yml', $ymlBefore);
        file_put_contents('app.ini', $iniBefore);
        exec('./RMT release -n', $output);
        $this->assertEquals($ymlAfter, file_get_contents('config.yml'));
        $this->assertEquals($iniAfter, file_get_contents('app.ini'));
    }
}
