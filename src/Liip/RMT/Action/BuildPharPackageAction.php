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

use Phar;
use FilesystemIterator;

use Liip\RMT\Context;

/**
 * Builds a Phar package of the current project.
 */
class BuildPharPackageAction extends BaseAction
{

    protected $releaseVersion;

    public function __construct($options)
    {
        $this->options = array_merge(array(
            'package-name' => 'rmt-package',
            'destination' => '/tmp/',
            'excluded-paths' => '',
            'metadata' => array(),
            'default-stub-cli' => '<?php __HALT_COMPILER(); ?>',
            'default-stub-web' => '<?php __HALT_COMPILER(); ?>',
        ), $options);
    }

    public function execute()
    {
        $packagePath = $this->create();

        $this->confirmSuccess();

        Context::get('output')->writeln('The package has been successfully created in: ' . $packagePath);
    }

    /**
     * Handles the creation of the package.
     */
    protected function create()
    {
        $this->setReleaseVersion();

        $output = $this->getDestination() . '/' . $this->getFilename();

        $phar = new Phar($output, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME);
        $phar->buildFromDirectory(Context::getParam('project-root'), $this->options['excluded-paths']);
        $phar->setMetadata(array_merge(['version' => $this->releaseVersion], $this->options['metadata']));
        $phar->setDefaultStub($this->options['default-stub-cli'], $this->options['default-stub-web']);

        return $output;
    }

    /**
     * Determines the package filename based on the next version and the 'package-name' option.
     *
     * @return string
     */
    protected function getFilename()
    {
        return $this->options['package-name'] . '-' . $this->releaseVersion . '.phar';
    }

    /**
     * Checks if the path is relative.
     *
     * @param $path string The path to check
     * @return bool
     */
    protected function isRelativePath($path)
    {
        return strpos($path, '/') !== 0;
    }

    /**
     * Get the destination directory to build the package into.
     *
     * @return string The destination
     */
    protected function getDestination()
    {
        $destination = $this->options['destination'];

        if ($this->isRelativePath($destination)) {
            return Context::getParam('project-root') . '/' . $destination;
        }

        return $destination;
    }

    /**
     * Determine and set the next release version.
     */
    protected function setReleaseVersion()
    {
        try {
            $currentVersion = Context::get('version-persister')->getCurrentVersion();
        } catch (\Exception $e) {
            $currentVersion = Context::get('version-generator')->getInitialVersion();
        }

        $this->releaseVersion = Context::get('version-generator')->generateNextVersion($currentVersion);
    }
}
