<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Unit;

// Used for Context tests
class ServiceClass
{
    private $options;

    public function __construct($options = null)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
