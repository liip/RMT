<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Config;

class Exception extends \Exception
{
    public function __construct($message)
    {
        parent::__construct('Config error: '.$message);
    }
}
