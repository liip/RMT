<?php
namespace Liip\RD;

class Config
{
    protected $fullConfig;
    protected $config;
    protected $env = 'default';

    public function __construct()
    {
        $this->fullConfig = $this->load();
    }

    protected function load()
    {
        // TODO: add auto-discover of rd.json file
        $file = realpath(__DIR__.'/../../../../../rd.json');
        return json_decode(file_get_contents($file), true);
    }

    protected function getConfigByEnv()
    {
        return $this->fullConfig[$this->env];
    }

    public function setEnv($env)
    {
        if (!array_key_exists($env, $this->fullConfig)) {
            throw new \Exception('the environment '. $env . ' is not defined');
        }
        $this->env = $env;
        $this->config = $this->getConfigByEnv($this->env);
    }

    public function getPreActions()
    {
        $actions = $this->config['pre-actions'];
        return $actions;
    }

    public function getVersionGenerator()
    {
        return $this->config['version_generator'];
    }
}

