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
        if (!array_key_exists('default', $this->fullConfig)) {
            throw new \Exception('the environment "default" should be defined');
        }
    }

    protected function load()
    {
        // TODO: add auto-discover of rd.json file
        $file = realpath(__DIR__.'/../../../../../rd.json');
        return json_decode(file_get_contents($file), true);
    }

    protected function getConfigByEnv()
    {
        $envConfig = $this->fullConfig[$this->env];
        if ($this->env !== 'default') {
            $defaultConfig = $this->fullConfig['default'];
            $envConfig = array_merge($defaultConfig, $envConfig);
        }
        return $envConfig;
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
        // TODO change this when we know of actions will be called
        return array();

        $actions = $this->config['pre-actions'];
        return $actions;
    }

    public function getVersionGenerator()
    {
        return $this->config['version_generator'];
    }

    public function getVersionPersister()
    {
        return $this->config['version_persister']['type'];
    }

    public function getVersionPersisterOptions()
    {
        return $this->config['version_persister']['options'];
    }

    public function getVCS()
    {
        return $this->config['vcs'];
    }

    public function getTagPrefix()
    {
        return $this->config['tag_prefix'];
    }
}

