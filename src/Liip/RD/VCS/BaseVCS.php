<?php

namespace Liip\RD\VCS;

abstract class BaseVCS implements \Liip\RD\VCS\VCSInterface
{
    protected $options;

    public function __construct($context = null, $options = array())
    {
        $this->options = $options;
    }
}
