<?php

namespace Liip\RD;

use Liip\RD\Command\ReleaseCommand;
use Liip\RD\Command\CurrentCommand;
use Liip\RD\Command\InitCommand;
use Liip\RD\Output\Output;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    // This ugly hack is mandatory to allow command to access application at configure() time
    // See Liip\Command\BaseCommand::getApplication()
    static $instance;

    public function __construct(){

        // Creation
        parent::__construct('Release Management Tool', '1.0');
        self::$instance = $this;

        // Change the current directory in favor of the project root folder,
        // this allow to run the task from outside the project like:
        //     $/home/www> myproject/RD release
        chdir($this->getProjectRootDir());

        // Add the default command
        $this->add(new InitCommand());

        // Add command that require the config file
        if (file_exists($this->getConfigFilePath())){
            $this->add(new ReleaseCommand());
            $this->add(new CurrentCommand());
        }
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return parent::run($input, new \Liip\RD\Output\Output());
    }

    public function getProjectRootDir()
    {
        if (defined('RD_ROOT_DIR')){
            return RD_ROOT_DIR;
        }
        else {
            return getcwd();
        }
    }

    public function getConfigFilePath()
    {
        return $this->getProjectRootDir().'/rd.json';
    }

}
