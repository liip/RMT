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
 * An updater that updates the version information stored in a PHP file (found by its class).
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
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class UpdateClassAction extends UpdateFileAction
{
    public function execute()
    {
        if (! isset($this->options['class'])) {
            throw new ConfigException('You must specify the class to update');
        }

        $this->updateClass(
            $this->options['class'],
            isset($this->options['pattern']) ? $this->options['pattern'] : null
        );
    }

    protected function updateClass($class, $pattern = null)
    {
        // see http://php.net/manual/en/language.oop5.basic.php for the regex
        if (! preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $class)) {
            throw new Exception('Configuration value "'. $class .'" is not a valid class name');
        }

        /*
         * If the class is not available in current context, we
         * try to load Composer autoloader in order to find it.
         */
        if (! class_exists($class)) {
            $loaderFile = $this->loadProjectComposerAutoload();

            // Not able to find Composer autoloader
            if (! $loaderFile) {
                throw new Exception(sprintf(
                    'Class %s not found (Composer autoloader was not found)',
                    $class
                ));
            }

            // Still doesn't exist after Composer loaded
            if (! class_exists($class)) {
                throw new Exception(sprintf(
                    'Class %s not found (Composer autoloader was found: %s)',
                    $class,
                    $loaderFile
                ));
            }
        }

        $reflection = new \ReflectionClass($class);
        $filename = $reflection->getFileName();

        $this->updateFile($filename, $pattern);
    }

    /**
     * @return string|null
     */
    private function loadProjectComposerAutoload()
    {
        $root = Context::getParam('project-root');

        $autoload = [
            $root.'/vendor/autoload.php',
            $root.'/../vendor/autoload.php',
            $root.'/../../autoload.php',
        ];

        foreach ($autoload as $file) {
            if (file_exists($file)) {
                require $file;
                return $file;
            }
        }

        return null;
    }
}
