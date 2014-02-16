<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Action;

use Liip\RMT\Exception;
use Liip\RMT\Config\Exception as ConfigException;

/**
 * An updater that updates the version information stored in a class file.
 *
 * this allows to use the UpdateVersionClassAction even with classes
 * who are not loaded at RMTs runtime. The difference is that
 * this will read a classfile param, rather than a class param, and use
 * this file parameter to determine what file to change, avoiding the reflection used
 * in UpdateVersionClassAction
 *
 * @see UpdateVersionClassAction
 */
class UpdateVersionClassFileAction extends UpdateVersionClassAction
{
    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function execute()
    {
        if (!isset($this->options['classfile'])) {
            throw new ConfigException('You must specify the classfile to update');
        }

        $this->updateFile($this->options['classfile']);
        $this->confirmSuccess();
    }
}
