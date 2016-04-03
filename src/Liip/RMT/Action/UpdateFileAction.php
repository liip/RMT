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
 * An updater that updates the version information stored in any kind of file.
 *
 * This file could be a configuration file (yml, json) or a package.json file
 * for instance.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class UpdateFileAction extends BaseAction
{
    public function execute()
    {
        if (! isset($this->options['file'])) {
            throw new ConfigException('You must specify the file to update');
        }

        $this->updateFile(
            $this->options['file'],
            isset($this->options['pattern']) ? $this->options['pattern'] : null
        );
    }

    protected function updateFile($filename, $pattern = null)
    {
        $current = Context::getParam('current-version');
        $next = Context::getParam('new-version');

        if (! file_exists($filename)) {
            throw new Exception('The path ' . $filename . ' does not exists');
        }

        if (! is_file($filename)) {
            throw new Exception('The path ' . $filename . ' must be a file');
        }

        $content = file_get_contents($filename);

        if (false === strpos($content, $current)) {
            throw new Exception('The file ' . $filename . ' does not contains the current version ' . $current);
        }

        if ($pattern) {
            $current = str_replace('%version%', $current, $pattern);
            $next = str_replace('%version%', $next, $pattern);
        }

        $content = str_replace($current, $next, $content);

        if (false === strpos($content, $next) || @file_put_contents($filename, $content) === false) {
            throw new Exception('The file ' . $filename . ' could not be updated with version ' . $next);
        }

        $this->confirmSuccess();
    }
}
