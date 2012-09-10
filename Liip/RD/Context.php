<?php

namespace Liip\RD;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Liip\RD\Version\Persister\VcsTagPersister;


class Context
{
    protected $preActions;
    protected $config;
    protected $input;
    protected $output;
    protected $currentVersion;
    protected $versionPersister;
    protected $versionGenerator;
    protected $userQuestions = array();
    protected $services = array();
    protected $params = array();
    protected $lists = array();

    public function init(Config $config)
    {
        $this->config = $config;

        //$this->preActions = $this->getPreActions();
        $this->currentVersion = $this->getVersionPersister()->getCurrentVersion();

        // we need to instantiate the version generator so that it registers its user questions
        $this->versionGenerator = $this->getVersionGenerator();

    }

    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getVersionPersister()
    {
        if (is_null($this->versionPersister)) {
            if ($this->config->getVersionPersister()=='vcs-tag'){
                $this->versionPersister = new VcsTagPersister($this->getVCS(), $this->getVersionGenerator()->getValidationRegex(), $this->config->getVersionPersisterOptions());
                return $this->versionPersister;
            }
            $class = ucwords($this->config->getVersionPersister());
            if (!class_exists($class)){
                $class = '\\Liip\\RD\\Version\\Persister\\'.$class.'Persister';
            }
            if (!class_exists($class)){
                throw new \Exception('Invalid persister defined: ['.$this->config->getVersionPersister().']');
            }
            $this->versionPersister = new $class($this, $this->config->getVersionPersisterOptions());
        }
        return $this->versionPersister;
    }

    public function getVersionGenerator()
    {
        if (is_null($this->versionGenerator)) {
            $class = ucwords($this->config->getVersionGenerator());
            if (!class_exists($class)){
                $class = '\\Liip\\RD\\Version\\Generator\\'.$class.'Generator';
            }
            $this->versionGenerator = new $class($this);
        }
        return $this->versionGenerator;
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

    public function getOutput()
    {
        return $this->output;
    }

    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * Register questions to ask to the user
     * @param String topic of the question (ie. comment)
     * @param UserQuestionInterface question object
     */
    public function addUserQuestion($topic, $question)
    {
        $this->userQuestions[$topic] = $question;
    }

    /**
     * Returns all user questions
     * @return UserQuestionInterface[] keyed on topic
     */
    public function getUserQuestions()
    {
        return $this->userQuestions;
    }

    /**
     * Returns a single question (probably used to get its answer)
     * @param topic of the question (see addUserQuestion)
     * @return UserQuestionInterface
     */
    public function getUserQuestionByTopic($topic)
    {
        return $this->userQuestions[$topic];
    }

    public function setProjectRoot($projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }

    public function getProjectRoot()
    {
        return $this->projectRoot;
    }

    public function setService($id, $class)
    {
        $this->services[$id] = $class;
    }

    public function getService($id)
    {
        if (!isset($this->services[$id])){
            throw new \InvalidArgumentException("There is no service define with id [$id]");
        }
        if (is_string($this->services[$id])) {
            $className = $this->services[$id];
            $this->services[$id] = new $className();
        }
        return $this->services[$id];
    }

    public function setParam($id, $value)
    {
        $this->params[$id] = $value;
    }

    public function getParam($id)
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

    public function addToList($id, $class)
    {
        if (!isset($this->lists[$id])){
            $this->createEmptyList($id);
        }
        $this->lists[$id][] = $class;
    }

    public function getList($id)
    {
        if (!isset($this->lists[$id])){
            throw new \InvalidArgumentException("There is no list define with id [$id]");
        }
        foreach ($this->lists[$id] as $pos => $className){
            if (is_string($className)) {
                $this->lists[$id][$pos] = new $className();
            }
        }
        return $this->lists[$id];
    }

}

