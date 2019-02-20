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

use Liip\RMT\Config\Exception as ConfigException;
use Liip\RMT\Context;
use Liip\RMT\Exception;
use ReflectionClass;

class FilesUpdateAction extends BaseAction
{
    /**
     * @throws ConfigException|Exception|\ReflectionException
     */
    public function execute()
    {
        if (count($this->options) === 0) {
            throw new ConfigException('You must specify at least one file');
        }

        foreach ($this->options as $option) {
            $file = $option[0];
            $pattern = isset($option[1]) ? $option[1] : null;

            if (! file_exists($file)) {
                $versionClass = new ReflectionClass($file);
                $file = $versionClass->getFileName();
            }

            if (! @file_get_contents($file)) {
                throw new ConfigException("Could not get the content of $file");
            }

            $this->updateFile($file, $pattern);
        }

        $this->confirmSuccess();
    }

    /**
     * will update a given filename with the current version
     *
     * @param string $filename
     * @param null $pattern
     * @throws Exception
     */
    protected function updateFile($filename, $pattern = null)
    {
        $current = Context::getParam('current-version');
        $next = Context::getParam('new-version');

        $content = file_get_contents($filename);
        if (false === strpos($content, $current)) {
            throw new Exception("The version file $filename does not contain the current version $current");
        }
        if ($pattern) {
            $current = str_replace('%version%', $current, $pattern);
            $next = str_replace('%version%', $next, $pattern);
        }

        $content = str_replace($current, $next, $content);

        if (false === strpos($content, (string)$next)) {
            throw new Exception("The version file $filename could not be updated with version $next");
        }
        file_put_contents($filename, $content);
    }
}