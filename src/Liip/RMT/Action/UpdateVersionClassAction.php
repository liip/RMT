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

use Liip\RMT\Context;
use Liip\RMT\Exception;
use Liip\RMT\Config\Exception as ConfigException;

/**
 * Legacy action used for Backward Compatibilty
 * 
 * TODO: Remove in 2.0
 *
 * UpdateVersionClass accepts either a generic file or PHP class
 * (which is resolved to a file using Reflection API). The found
 * file is then updated with the new version number.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class UpdateVersionClassAction extends UpdateClassAction
{
    public function execute()
    {
        trigger_error(
            'update-version-class action is deprecated and will be removed in 2.0. ' .
            'Use either update-class or update-file actions instead.',
            E_USER_DEPRECATED
        );

        if (! isset($this->options['class'])) {
            throw new ConfigException('You must specify the class to update');
        }

        $class = $this->options['class'];

        if (file_exists($class)) {
            $this->updateFile($class);
            return;
        }

        $this->updateClass($class);
    }
}
