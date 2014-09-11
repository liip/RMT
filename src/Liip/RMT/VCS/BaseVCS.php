<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\VCS;

abstract class BaseVCS implements \Liip\RMT\VCS\VCSInterface
{
    protected $options;

    public function __construct($options = array())
    {
        $this->options = $options;
    }
}
