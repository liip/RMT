<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2017, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('SKIP_HG_TESTS')) {
    exec('which hg', $result);
    define('SKIP_HG_TESTS', !isset($result[0]));
}

if (!defined('SKIP_GIT_TESTS')) {
    exec('which git', $result);
    define('SKIP_GIT_TESTS', !isset($result[0]));
}