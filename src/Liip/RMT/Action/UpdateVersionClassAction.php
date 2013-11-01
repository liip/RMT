<?php
namespace Liip\RMT\Action;

use Liip\RMT\Context;
use Liip\RMT\Action\BaseAction;
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
    private $options;

    public function __construct($options)
    {
        if (!isset($options['class'])) {
            throw new ConfigException('You must specify the class to update');
        }
        $this->options = $options;
    }

    public function execute()
    {
        $current = Context::getParam('current-version');
        $next = Context::getParam('new-version');
        $versionClass = new \ReflectionClass($this->options['class']);
        $content = file_get_contents($versionClass->getFileName());
        if (false === strpos($content, $current)) {
            throw new Exception('The version class ' . $versionClass->getFileName() . " does not contain the current version $current");
        }
        if (isset($this->options['pattern'])) {
            $current = str_replace('%version%', $current, $this->options['pattern']);
            $next = str_replace('%version%', $next, $this->options['pattern']);
        }
        $content = str_replace($current, $next, $content);
        file_put_contents($versionClass->getFileName(), $content);
    }
}
