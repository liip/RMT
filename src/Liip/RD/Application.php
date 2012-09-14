<?php

namespace Liip\RD;

use Liip\RD\ReleaseCommand;


use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{

    public function __construct(){
        parent::__construct('RD', '1.0');
        $this->add(new ReleaseCommand());
    }

}
