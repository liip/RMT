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
    protected $currentVersion;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->config = new Config();
        $this->config->setEnv($input->getOption('config'));
        //$this->preActions = $this->getPreActions();
        $this->input = $input;
        $this->output = $output;
        $this->currentVersion = $this->getVersionPersister()->getCurrentVersion();
    }

    public function getVersionPersister()
    {
        $class = ucwords($this->config->getVersionPersister());
        if (!class_exists($class)){
            $class = '\\Liip\\RD\\Version\\Persister\\'.$class.'Persister';
        }
        $persister = new $class();
        return $persister;
    }

    public function getVersionGenerator()
    {
        $class = ucwords($this->config->getVersionGenerator());
        if (!class_exists($class)){
            $class = '\\Liip\\RD\\Version\\Generator\\'.$class.'Generator';
        }
        $generator = new $class();
        return $generator;
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
        return $this->currentVersion;
    }
}
