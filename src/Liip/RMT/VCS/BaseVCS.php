<?php

namespace Liip\RMT\VCS;

abstract class BaseVCS implements \Liip\RMT\VCS\VCSInterface
{
    protected $options;

    public function __construct($options = array())
    {
        $this->options = $options;
    }
}

