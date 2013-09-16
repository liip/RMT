<?php

namespace Liip\RMT;


class Context
{
    protected $services = array();
    protected $params = array();
    protected $lists = array();

    private static $instance;

    protected function __construct()
    {
    }

    /**
     * @return Context
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Context();
        }
        return self::$instance;
    }

    public function setService($id, $classOrObject, $options = null)
    {
        if (is_object($classOrObject)){
            $this->services[$id] = $classOrObject;
        }
        else if (is_string($classOrObject)) {
            $this->validateClass($classOrObject);
            $this->services[$id] = array($classOrObject, $options);
        }
        else {
            throw new \InvalidArgumentException("setService() only accept an object or a valid class name");
        }
    }

    public function getService($id)
    {
        if (!isset($this->services[$id])){
            throw new \InvalidArgumentException("There is no service define with id [$id]");
        }
        if (is_array($this->services[$id])) {
            $this->services[$id] = $this->instanciateObject($this->services[$id]);
        }
        return $this->services[$id];
    }

    public function setParameter($id, $value)
    {
        $this->params[$id] = $value;
    }

    public function getParameter($id)
    {
        if (!isset($this->params[$id])){
            throw new \InvalidArgumentException("There is no param define with id [$id]");
        }
        return $this->params[$id];
    }

    public function createEmptyList($id)
    {
        $this->lists[$id] = array();
    }

    public function addToList($id, $class, $options = null)
    {
        $this->validateClass($class);
        if (!isset($this->lists[$id])){
            $this->createEmptyList($id);
        }
        $this->lists[$id][] = array($class, $options);
    }

    public function getList($id)
    {
        if (!isset($this->lists[$id])){
            throw new \InvalidArgumentException("There is no list define with id [$id]");
        }
        foreach ($this->lists[$id] as $pos => $object){
            if (is_array($object)) {
                $this->lists[$id][$pos] = $this->instanciateObject($object);
            }
        }
        return $this->lists[$id];
    }

    protected function instanciateObject($objectDefinition)
    {
        list($className, $options) = $objectDefinition;
        return new $className($options);
    }

    protected function validateClass($className)
    {
        if (!class_exists($className)){
            throw new \InvalidArgumentException("The class [$className] does not exist");
        }
    }

    /**
     * Shortcut to retried a service
     * */
    public static function get($serviceName)
    {
        return self::getInstance()->getService($serviceName);
    }

    /**
     * Shortcut to retried a parameter
     * */
    public static function getParam($name)
    {
        return self::getInstance()->getParameter($name);
    }


}

