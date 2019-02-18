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
 * An updater that updates the version information stored in a class.
 *
 * Typically this would be a class defining a constant for client code to check
 * the version of the library they are using.
 *
 * An example Version class might look like this:
 *
 * class Version
 * {
 *     const VERSION = '1.0.0-beta-4';
 * }
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class UpdateVersionClassAction extends BaseAction
{
    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function execute()
    {
        $filelist = array();
        if (isset($this->options['class'])) {
            $filelist[] = $this->getFilename($this->options['class']);
        } else if (!empty($this->options)) {
            foreach ($this->options as $key => $section) {
                if (!isset($section['class'])) {
                    throw new ConfigException('You must specify the class or file to update');
                }
                $filelist[] = $this->getFilename($section['class']);
            }
        } else {
            throw new ConfigException('You must specify the class or file to update');
        }

        $this->updateFiles($filelist);
        $this->confirmSuccess();
    }

    /**
     * converts a class name to a file path, but keeps file paths as is
     *
     * @param string $class the class name or file path
     * @return string the file path
     * @throws \ReflectionException
     */
    protected function getFilename($class)
    {
        if (file_exists($class)) {
            return $class;
        } else {
            $versionClass = new \ReflectionClass($class);
            return $versionClass->getFileName();
        }
    }

    /**
     * will update all given files with the current version
     *
     * @param array $filelist list of all files to update
     *
     * @throws \Liip\RMT\Exception
     * @see #updateFile
     */
    protected function updateFiles($filelist)
    {
        foreach ($filelist as $key => $file) {
            $this->updateFile($file);
        }
    }

    /**
     * will update a given filename with the current version
     *
     * @param string $filename
     *
     * @throws \Liip\RMT\Exception
     */
    protected function updateFile($filename)
    {
        $current = Context::getParam('current-version');
        $next = Context::getParam('new-version');

        $content = file_get_contents($filename);
        if (false === strpos($content, $current)) {
            throw new Exception('The version class ' . $filename . " does not contain the current version $current");
        }
        if (isset($this->options['pattern'])) {
            $current = str_replace('%version%', $current, $this->options['pattern']);
            $next = str_replace('%version%', $next, $this->options['pattern']);
        }
        $content = str_replace($current, $next, $content);
        if (false === strpos($content, $next)) {
            throw new Exception('The version class ' . $filename . " could not be updated with version $next");
        }
        file_put_contents($filename, $content);
    }
}
