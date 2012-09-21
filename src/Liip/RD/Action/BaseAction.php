<?php

namespace Liip\RD\Action;

abstract class BaseAction
{
    abstract public function execute($context);

    public function getTitle()
    {
        $className = get_class($this);
        return str_replace('Action', '', $className);
    }

    public function getOptions()
    {
        return array();
    }
}