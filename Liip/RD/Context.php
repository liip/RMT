<?php

namespace Liip\RD;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Context
{
    protected $preActions;
    protected $config;
    protected $input;
    protected $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->config = new Config();
        $this->config->setEnv($input->getOption('config'));
        $this->preActions = $this->getPreActions();
        $this->input = $input;
        $this->output = $output;
    }

    public function getPreActions()
    {
        $actions = array();
        foreach ($this->config->getPreActions() as $action){
            $class = $action['name'];
            if (!class_exists($class)){
                $class = '\\Liip\\RD\\PreReleaseAction\\'.$class;
            }
            $actions[] = new $class($action['options']);
        }
        return $actions;
    }

    public function getVCS()
    {
        // VCS
        $class = strtoupper($this->config->getVCS());
        if (!class_exists($class)){
            $class = '\\Liip\\RD\\VCS\\'.$class;
        }
        $vcs = new $class();
        return $vcs;
    }

    /// $versionPersister = new ChangelogManager(__DIR__.'/../../CHANGELOG');


    public function getOutput()
    {
        return $this->output;
    }

    public function getCurrentVersion()
    {
        return '1.0';
    }
}
