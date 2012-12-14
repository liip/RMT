<?php
namespace Liip\RMT\Config;

class Exception extends \Exception
{
    public function __construct($message)
    {
        parent::__construct('Config error: '.$message);
    }
}

