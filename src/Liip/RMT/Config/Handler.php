<?php

namespace Liip\RMT\Config;

/**
 * Read, parse and validate configuration file
 */
class Handler
{
    public function __construct($rawConfig = null, $projectRoot = null)
    {
        $this->rawConfig = $rawConfig;
        $this->projectRoot = $projectRoot;
    }

    public function getDefaultConfig()
    {
        return array(
            "vcs" => null,
            "prerequisites" => array(),
            "pre-release-actions" => array(),
            "version-generator" => null,
            "version-persister" => null,
            "post-release-actions" => array(),
            "branch-specific" => array()
        );
    }

    public function getConfigForBranch($branchName)
    {
        return $this->prepareConfigFor($branchName);
    }

    public function getBaseConfig()
    {
        return $this->prepareConfigFor(null);
    }

    protected function prepareConfigFor($branch)
    {
        $config = $this->mergeConfig($branch);
        $config = $this->normalize($config);

        return $config;
    }

    protected function mergeConfig($branchName = null)
    {
        $defaultConfig = $this->getDefaultConfig();
        $config = array_merge($defaultConfig, $this->rawConfig);
        if (isset($branchName) && isset($config['branch-specific'][$branchName])) {
            $envSpecific = $config['branch-specific'][$branchName];
            $config = array_merge($config, $envSpecific);
        }
        unset($config['branch-specific']);

        return $config;
    }

    /**
     * Normalize all config entry to be a normalize class entry: array("class"=>XXX, "options"=>YYY)
     */
    protected function normalize($config)
    {
        // Validate the config entry
        $this->validateRootElements($config);

        // Normalize all class name and options, remove null entry
        foreach (array("vcs", "version-generator", "version-persister") as $configKey){
            if ($config[$configKey] == null){
                unset($config[$configKey]);
            }
            else {
                $config[$configKey] = $this->getClassAndOptions($config[$configKey], $configKey);
            }
        }
        foreach (array("prerequisites", "pre-release-actions", "post-release-actions") as $configKey){
            foreach($config[$configKey] as $pos => $item){
                $config[$configKey][$pos] = $this->getClassAndOptions($config[$configKey][$pos], $configKey.'_'.$pos);
            }
        }

        return $config;
    }

    protected function validateRootElements($config)
    {
        // Check for extra keys
        $extraKeys = array_diff(array_keys($config),array_keys($this->getDefaultConfig()));
        if (count($extraKeys) > 0){
            throw new Exception('key(s) ['.implode(', ',$extraKeys).'] are invalid, must be ['.implode(', ',array_keys($this->getDefaultConfig())).']');
        }

        // Check for missing keys
        foreach(array("version-generator", "version-persister") as $mandatoryParam){
            if ($config[$mandatoryParam] == null) {
                throw new Exception("[$mandatoryParam] should be defined");
            }
        }
    }

    /**
     * Sub part of the normalize()
     */
    protected function getClassAndOptions($rawConfig, $sectionName)
    {
        if ( is_string($rawConfig)){
            $class = $this->findClass($rawConfig, $sectionName);
            $options = array();
        }
        else if ( is_array($rawConfig)){
            if (isset($rawConfig['name'])){
                $class = $this->findClass($rawConfig['name'], $sectionName);
                unset($rawConfig['name']);
            }
            else {
                throw new Exception("Missing information for [$sectionName], you must provide a [name] value");
            }
            $options = $rawConfig;
        }
        else {
            throw new Exception("Invalid configuration for [$sectionName] should be a object name or an array with name and options");
        }

        return array("class"=>$class, "options"=>$options);
    }

    /**
     * Sub part of the normalize()
     */
    protected function findClass($name, $sectionName)
    {
        $file = $this->projectRoot.DIRECTORY_SEPARATOR.$name;
        if (strpos($file, '.php') > 0){
            if (file_exists($file)) {
                require_once $file;
                $parts = explode(DIRECTORY_SEPARATOR, $file);
                $lastPart = array_pop($parts);
                return str_replace('.php', '', $lastPart);
            }
            else {
                throw new \Liip\RMT\Exception("Impossible to open [$file] please review your config");
            }
        }
        return $this->findInternalClass($name, $sectionName);
    }

    /**
     * Sub part of the normalize()
     */
    protected function findInternalClass($name, $sectionName)
    {
        // Remove list id like xxx_3
        $classType = $sectionName;
        if (strpos($classType, '_') !== false){
            $classType = substr($classType, 0, strpos($classType, '_'));
        }

        // Guess the namespace
        $namespacesByType = array(
            'vcs' => 'Liip\RMT\VCS',
            'prerequisites' => 'Liip\RMT\Prerequisite',
            'pre-release-actions' => 'Liip\RMT\Action',
            'post-release-actions' => 'Liip\RMT\Action',
            "version-generator" => 'Liip\RMT\Version\Generator',
            "version-persister" => 'Liip\RMT\Version\Persister'
        );
        $nameSpace = $namespacesByType[$classType];

        // Guess the class name
        // Convert from xxx-yyy-zzz to XxxYyyZzz and append suffix
        $suffixByType = array(
            'vcs' => '',
            'prerequisites' => '',
            'pre-release-actions' => 'Action',
            'post-release-actions' => 'Action',
            "version-generator" => 'Generator',
            "version-persister" => 'Persister'
        );
        $nameSpace = $namespacesByType[$classType];
        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $name))).$suffixByType[$classType];

        return $nameSpace.'\\'.$className;
    }
}

