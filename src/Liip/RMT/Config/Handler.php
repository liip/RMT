<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Config;

/**
 * Read, parse and validate configuration file
 */
class Handler
{
    const ACTIONS_LIST = ['prerequisites', 'pre-release-actions', 'post-release-actions'];

    public function __construct($rawConfig = null, $projectRoot = null)
    {
        $this->rawConfig = $rawConfig;
        $this->projectRoot = $projectRoot;
    }

    public function getDefaultConfig()
    {
        return array(
            'vcs' => null,
            'prerequisites' => array(),
            'pre-release-actions' => array(),
            'version-generator' => null,
            'version-persister' => null,
            'post-release-actions' => array(),
            'branch-specific' => array(),
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
        // Handling the two different config mode (with 'branch-specific' or with '_default' section)
        // See https://github.com/liip/RMT/issues/56 for more info
        if (array_key_exists('_default', $this->rawConfig)) {
            $baseConfig = array_merge($this->getDefaultConfig(), $this->rawConfig['_default']);
            unset($baseConfig['branch-specific']);
            $branchesConfig = $this->rawConfig;
            unset($branchesConfig['_default']);
        } else {
            $baseConfig = array_merge($this->getDefaultConfig(), $this->rawConfig);
            $branchesConfig = $baseConfig['branch-specific'];
            unset($baseConfig['branch-specific']);
        }

        // Return custom branch config
        if (isset($branchName) && isset($branchesConfig[$branchName])) {
            return $this->replaceArraysValuesWhileKeepingOrderOfActionsLists($baseConfig, $branchesConfig[$branchName]);
        }

        return $baseConfig;
    }

    protected function replaceArraysValuesWhileKeepingOrderOfActionsLists($baseConfig, $branchConfig)
    {
        // array_replace_recursive messes up the order of elements. Let's fix that.
        $config = array_replace_recursive($baseConfig, $branchConfig);

        // Loop over actions list, for which the order of elements is crucial
        foreach (static::ACTIONS_LIST as $actionKey) {

            // The config may not contain all actions lists configuration keys
            if (!isset($config[$actionKey]) || !isset($branchConfig[$actionKey])) {
                continue;
            }

            // Compare the keys of both arrays. If they are the same already, there's nothing to do.
            $branchConfigActionKeys = array_keys($branchConfig[$actionKey]);
            if ($branchConfigActionKeys === array_keys($config[$actionKey])) {
                continue;
            }

            // Otherwise, loop over the branch config keys (as reference) and recreate an array with this order
            $reorderedActionConfig = [];
            foreach ($branchConfigActionKeys as $key) {
                $reorderedActionConfig[$key] = $config[$actionKey][$key];
            }
            $config[$actionKey] = $reorderedActionConfig;
        }

        return $config;
    }

    /**
     * Normalize all config entry to be a normalize class entry: array("class"=>XXX, "options"=>YYY)
     */
    protected function normalize($config)
    {
        // Validate the config entry
        $this->validateRootElements($config);

        // For single value elements, normalize all class name and options, remove null entry
        foreach (array('vcs', 'version-generator', 'version-persister') as $configKey) {
            $value = $config[$configKey];
            if ($value == null) {
                unset($config[$configKey]);
                continue;
            }
            $config[$configKey] = $this->getClassAndOptions($value, $configKey);
        }

        // Same process but for list value elements
        foreach (static::ACTIONS_LIST as $configKey) {
            foreach ($config[$configKey] as $key => $item) {

                // Accept the element to be define by key or by value
                if (!is_numeric($key)) {
                    if ($item == null) {
                        $item = array();
                    }
                    $item['name'] = $key;
                }

                $config[$configKey][$key] = $this->getClassAndOptions($item, $configKey.'_'.$key);
            }
        }

        return $config;
    }

    protected function validateRootElements($config)
    {
        // Check for extra keys
        $extraKeys = array_diff(array_keys($config), array_keys($this->getDefaultConfig()));
        if (count($extraKeys) > 0) {
            $extraKeys = implode(', ', $extraKeys);
            $validKeys = implode(', ', array_keys($this->getDefaultConfig()));
            throw new Exception('key(s) ['.$extraKeys.'] are invalid, must be ['.$validKeys.']');
        }

        // Check for missing keys
        foreach (array('version-generator', 'version-persister') as $mandatoryParam) {
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
        if (is_string($rawConfig)) {
            $class = $this->findClass($rawConfig, $sectionName);
            $options = array();
        } elseif (is_array($rawConfig)) {

            // Handling Yml corner case (see https://github.com/liip/RMT/issues/54)
            if (count($rawConfig) == 1 && key($rawConfig) !== 'name') {
                $name = key($rawConfig);
                $rawConfig = is_array(reset($rawConfig)) ? reset($rawConfig) : array();
                $rawConfig['name'] = $name;
            }

            if (!isset($rawConfig['name'])) {
                throw new Exception("Missing information for [$sectionName], you must provide a [name] value");
            }

            $class = $this->findClass($rawConfig['name'], $sectionName);
            unset($rawConfig['name']);

            $options = $rawConfig;
        } else {
            throw new Exception("Invalid configuration for [$sectionName] should be a object name or an array with name and options");
        }

        return array('class' => $class, 'options' => $options);
    }

    /**
     * Sub part of the normalize()
     */
    protected function findClass($name, $sectionName)
    {
        $file = $this->projectRoot.DIRECTORY_SEPARATOR.$name;
        if (strpos($file, '.php') > 0) {
            if (file_exists($file)) {
                require_once $file;
                $parts = explode(DIRECTORY_SEPARATOR, $file);
                $lastPart = array_pop($parts);

                return str_replace('.php', '', $lastPart);
            } else {
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
        if (strpos($classType, '_') !== false) {
            $classType = substr($classType, 0, strpos($classType, '_'));
        }

        // Guess the namespace
        $namespacesByType = array(
            'vcs' => 'Liip\RMT\VCS',
            'prerequisites' => 'Liip\RMT\Prerequisite',
            'pre-release-actions' => 'Liip\RMT\Action',
            'post-release-actions' => 'Liip\RMT\Action',
            'version-generator' => 'Liip\RMT\Version\Generator',
            'version-persister' => 'Liip\RMT\Version\Persister',
        );
        $nameSpace = $namespacesByType[$classType];

        // Guess the class name
        // Convert from xxx-yyy-zzz to XxxYyyZzz and append suffix
        $suffixByType = array(
            'vcs' => '',
            'prerequisites' => '',
            'pre-release-actions' => 'Action',
            'post-release-actions' => 'Action',
            'version-generator' => 'Generator',
            'version-persister' => 'Persister',
        );
        $nameSpace = $namespacesByType[$classType];
        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $name))).$suffixByType[$classType];

        return $nameSpace.'\\'.$className;
    }
}
