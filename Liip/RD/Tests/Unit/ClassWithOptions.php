<?php

namespace Liip\RD\Tests\Unit;

// Used for Context tests
class ClassWithOptions
{
    private $options;
    public function __construct($options){ $this->options = $options; }
    public function getOptions(){ return $this->options; }
}