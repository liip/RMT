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
        if (!isset($this->options['class'])) {
            throw new ConfigException('You must specify the class or file to update');
        }

        if (file_exists($this->options['class'])) {
            $filename = $this->options['class'];
        } else {
            $versionClass = new \ReflectionClass($this->options['class']);
            $filename = $versionClass->getFileName();
        }

        $this->updateFile($filename);
        $this->confirmSuccess();
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
