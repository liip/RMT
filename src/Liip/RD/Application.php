<?php

namespace Liip\RD;

use Liip\RD\Command\ReleaseCommand;
use Liip\RD\Command\CurrentCommand;
use Liip\RD\Command\InitCommand;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{

    public function __construct(){
        parent::__construct('RD', '1.0');
        $this->add(new ReleaseCommand());
        $this->add(new CurrentCommand());
        $this->add(new InitCommand());
    }

}
