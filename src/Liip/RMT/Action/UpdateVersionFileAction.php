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
 * An updater that updates the version information stored in plain file.
 *
 * The content of the given file will be replaced by the current version.
 *
 * @author Gilles Crettenand <gilles@crettenand.info>
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 */
class UpdateVersionFileAction extends BaseAction
{
    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function execute()
    {
        if (!isset($this->options['file'])) {
            throw new ConfigException('You must specify the file to update');
        }

        $filename = $this->options['file'];
        if (! file_exists($filename)) {
            throw new Exception("The file '$filename' does not exists.");
        }

        // Get the new tag
        $gitTag = Context::get('version-persister')->getTagFromVersion(Context::getInstance()->getParam('new-version'));

        file_put_contents($filename, $gitTag);
        $this->confirmSuccess();
    }
}
