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
     *
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
     * This method is called after all registered information collectors have
     * been called to validate that the action has all necessary information
     * if anything is missing an exception should be thrown.
     * When called, the 'current-version' and 'new-version' parameters are
     * already known, so a check can be made on those.
     *
     * @throws \Exception
     */
    public function validateContext()
    {

    }

    /**
     * A common method to confirm success to the user
     */
    public function confirmSuccess()
    {
        Context::get('output')->writeln('<info>OK</info>');
    }
}
