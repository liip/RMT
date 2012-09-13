<?php
namespace Liip\RD\Config;

class Exception extends \Exception
{
    public function __construct($message)
    {
        parent::__construct('Config error: '.$message);
    }
}