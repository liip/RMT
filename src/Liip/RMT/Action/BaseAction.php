<?php

namespace Liip\RMT\Action;

use Liip\RMT\Context;

abstract class BaseAction
{
    protected $options = array();

    public function __construct($options = array())
    {
        $this->options = $options;
    }

    /**
     * Main part of the action
     */
    abstract public function execute();

    /**
     * Return the name of the action as it will be display to the user
     * @return string
     */
    public function getTitle()
    {
        $nsAndclass = explode('\\', get_class($this));
        return preg_replace('/(?!^)[[:upper:]][[:lower:]]/', ' $0', preg_replace('/(?!^)[[:upper:]]+/', '$0', end($nsAndclass)));
    }

    /**
     * Return an array of options that can be
     *   * Liip\RMT\Option\Option    A new option specific to this prerequiste
     *   * string                   The name of a standarmt option (comment, type, author...)
     *
     * @return array
     */
    public function getInformationRequests()
    {
        return array();
    }

    /**
     * A common method to confirm success to the user
     */
    public function confirmSuccess()
    {
        Context::get('output')->writeln('<info>OK</info>');
    }
}

