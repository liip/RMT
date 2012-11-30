<?php

namespace Liip\RD\Tests\Unit;

// Used for Context tests
class ServiceClass
{
    private $options;
    public function __construct($options = null){ $this->options = $options; }
    public function getOptions(){ return $this->options; }
}