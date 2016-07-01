<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2016, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Prerequisite;

use Liip\RMT\Prerequisite\ComposerScriptCheck;

class ComposerScriptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp "No Composer scripts provided"
     */
    public function testExceptionWhenNoScriptsProvided()
    {
        $options = array(
            'composer' => 'composer'
        );

        new ComposerScriptCheck($options);
    }
}
