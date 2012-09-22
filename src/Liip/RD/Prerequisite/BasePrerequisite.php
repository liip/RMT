<?php

namespace Liip\RD\Prerequisite;

abstract class BasePrerequisite {

    abstract public function execute($context);

    /**
     * Return a array of options that can be
     *   * Liip\RD\Option\Option    A new option specific to this prerequiste
     *   * string                   The name of a standard option (command, type, author...)
     *
     * @return array
     */
    public function getInformationRequests()
    {
        return array();
    }
}