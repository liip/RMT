<?php
namespace Liip\RD;

class Config
{
    protected $fullConfig;
    protected $config;
    protected $env = 'default';

    public function __construct($rawConfig)
    {
        $this->fullConfig = $rawConfig;
        if (!array_key_exists('default', $this->fullConfig)) {
            throw new \InvalidArgumentException('the environment "default" should be defined');
        }
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
            $env = 'default';
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
        if ( is_string($this->config['version_persister'])){
            return $this->config['version_persister'];
        }
        return $this->config['version_persister']['type'];
    }

    public function getVersionPersisterOptions()
    {
        if ( is_string($this->config['version_persister'])){
            return array();
        }
        else if ( is_array($this->config['version_persister'])){
            $options = $this->config['version_persister'];
            unset($options['type']);
            return $options;
        }
    }

    public function getVCS()
    {
        if (!isset($this->config['vcs'])){
            throw new \InvalidArgumentException('No [vcs] define in the config');
        }
        return $this->config['vcs'];
    }

    public function getTagPrefix()
    {
        return $this->config['tag_prefix'];
    }
}

