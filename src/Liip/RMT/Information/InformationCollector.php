<?php

namespace Liip\RMT\Information;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Collect user info
 */
class InformationCollector
{
    static $standardRequests = array(
        'comment' => array(
            'description' => 'Comment associated with the release',
            'type' => 'text'
        ),
        'type' => array(
            'description' => 'Release type, can be major, minor or patch',
            'type' => 'choice',
            'choices' => array('major', 'minor', 'patch'),
            'choices_shortcuts' => array('m'=>'major', 'i'=>'minor', 'p'=>'patch'),
            'default' => 'patch'
        )
    );

    protected $requests = array();
    protected $values = array();

    public function registerRequest($request)
    {
        $name = $request->getName();
        if (in_array($name, static::$standardRequests)){
            throw new \Exception("Request [$name] is reserved as a standard request name, choose an other name please");
        }

        if ($this->hasRequest($name)){
            throw new \Exception("Request [$name] already registered");
        }

        $this->requests[$name] = $request;
    }

    public function registerRequests($list)
    {
        foreach($list as $request) {
            if (is_string($request)){
                $this->registerStandardRequest($request);
            }
            else if ($request instanceof InformationRequest){
                $this->registerRequest($request);
            }
            else {
                throw new \Exception("Invalid request, must a Request class or a string for standard requests");
            }
        }
    }

    public function registerStandardRequest($name)
    {
        if (!in_array($name, array_keys(static::$standardRequests))){
            throw new \Exception("There is no standard request named [$name]");
        }
        if (!isset($this->requests[$name])) {
            $this->requests[$name] = new InformationRequest($name, static::$standardRequests[$name]);
        }
    }

    /**
     * @return InformationRequest
     */
    public function getRequest($name)
    {
        if (!$this->hasRequest($name)){
            throw new \InvalidArgumentException("There is no information request named [$name]");
        }
        return $this->requests[$name];
    }

    public function hasRequest($name)
    {
        return isset($this->requests[$name]);
    }

    /**
     * Return a set of command request, converted from the Base Request
     *
     * @return InputOption[]
     */
    public function getCommandOptions()
    {
        $consoleOptions = array();
        foreach($this->requests as $name => $request){
            if ($request->isAvailableAsCommandOption()){
                $consoleOptions[$name] = $request->convertToCommandOption();
            }
        }
        return $consoleOptions;
    }

    public function hasMissingInformation()
    {
        foreach ($this->requests as $request){
            if (!$request->hasValue()) {
                return true;
            }
        }
        return false;
    }

    public function getInteractiveQuestions()
    {
        $questions = array();
        foreach($this->requests as $name => $request){
            if ($request->isAvailableForInteractive() && !$request->hasValue()){
                $questions[$name] = $request->convertToInteractiveQuestion();
            }
        }
        return $questions;
    }

    public function handleCommandInput(InputInterface $input)
    {
        foreach ($input->getOptions() as $name => $value){
            if ($this->hasRequest($name) && $this->getRequest($name)->getOption('default') !== $value){
                $this->getRequest($name)->setValue($value);
            }
        }
    }

    public function setValueFor($requestName, $value){
        return $this->getRequest($requestName)->setValue($value);
    }

    public function getValueFor($requestName, $default = null){
        if ($this->hasRequest($requestName)){
            return $this->getRequest($requestName)->getValue();
        }
        else {
            if (func_num_args() == 2){
                return $default;
            }
            throw new \Exception("No request named $requestName");
        }
    }
}

