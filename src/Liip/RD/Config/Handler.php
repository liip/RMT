<?php

namespace Liip\RD\Config;

use Liip\RD\Context;

class Handler
{
    protected $fullConfig;
    protected $config;
    protected $env = 'default';

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

    public function createContext($config, $env = null)
    {
        // Parse and normalize config
        $config = $this->merge($config, $env);
        $this->validateRootElements($config);
        $config = $this->normalize($config);

        // Create the context and populate it
        $context = new Context();
        foreach (array("vcs", "version-generator", "version-persister") as $service){
            if (isset($config[$service])){
                $context->setService($service, $config[$service]['class'], $config[$service]['options']);
            }
        }
        foreach (array("prerequisites", "pre-release-actions", "post-release-actions") as $listName){
            $context->createEmptyList($listName);
            foreach ($config[$listName] as $service){
                $context->addToList($listName, $service['class'], $service['options']);
            }
        }

        return $context;
    }

    /**
     * Merge the 3 config level: default, user config and potentially branch specific
     */
    public function merge($rawConfig, $env = null)
    {
        $defaultConfig = $this->getDefaultConfig();
        $config = array_merge($defaultConfig, $rawConfig);

        if (isset($config['branch-specific'][$env])){
            $envSpecific = $config['branch-specific'][$env];
            unset($config['branch-specific']);
            $config = array_merge($config, $envSpecific);
        }

        return $config;
    }

    public function validateRootElements($config)
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
     * Normalize all config entry to be a normalize class entry: array("class"=>XXX, "options"=>YYY)
     */
    public function normalize($config)
    {
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

    /**
     * Sub part of the normalize()
     */
    protected function getClassAndOptions($rawConfig, $sectionName)
    {
        if ( is_string($rawConfig)){
            $class = $rawConfig;
            if (!class_exists($class)){
                $class = $this->findInternalClass($class, $sectionName);
            }
            $options = array();
        }
        else if ( is_array($rawConfig)){
            if (isset($rawConfig['class'])){
                $class = $rawConfig['class'];
                unset($rawConfig['class']);
            }
            else if (isset($rawConfig['type'])){
                $class = $this->findInternalClass($rawConfig['type'], $sectionName);
                unset($rawConfig['type']);
            }
            else {
                throw new Exception("Missing information for [$sectionName], you must provide a [type] or a [class] value");
            }
            $options = $rawConfig;
        }
        else {
            throw new Exception("Invalid configuration for [$sectionName] should be a class name or an array with class and options");
        }

        return array("class"=>$class, "options"=>$options);
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
            'vcs' => 'Liip\RD\VCS',
            'prerequisites' => 'Liip\RD\Prerequisite',
            'pre-release-actions' => 'Liip\RD\Action',
            'post-release-actions' => 'Liip\RD\Action',
            "version-generator" => 'Liip\RD\Version\Generator',
            "version-persister" => 'Liip\RD\Version\Persister'
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

