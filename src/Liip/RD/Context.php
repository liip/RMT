<?php

namespace Liip\RD;


class Context
{
    protected $services = array();
    protected $params = array();
    protected $lists = array();

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
        $service = \Liip\ArrayHelper::get($this->services, $id, null, 'There is no service define with id [%s]');
        if (is_array($service)) {
            $this->services[$id] = ($service = $this->instanciateObject($service));
        }
        return $service;
    }

    public function setParam($id, $value)
    {
        $this->params[$id] = $value;
    }

    public function getParam($id)
    {
        return \Liip\ArrayHelper::get($this->params, $id, null, 'There is no param define with id [%s]');
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
        return new $className($this, $options);
    }

    protected function validateClass($className)
    {
        if (!class_exists($className)){
            throw new \InvalidArgumentException("The class [$className] does not exist");
        }
    }

}

