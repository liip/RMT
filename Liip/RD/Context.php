<?php

namespace Liip\RD;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


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

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->config = new Config();
        $this->config->setEnv($input->getOption('config'));
        //$this->preActions = $this->getPreActions();
        $this->input = $input;
        $this->output = $output;
        $this->currentVersion = $this->getVersionPersister()->getCurrentVersion();
    }

    public function getVersionPersister()
    {
        if (is_null($this->versionPersister)) {
            $class = ucwords($this->config->getVersionPersister());
            if (!class_exists($class)){
                $class = '\\Liip\\RD\\Version\\Persister\\'.$class.'Persister';
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
            $this->versionGenerator = new $class();
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
     * @return array of UserQuestionInterface, keyed on topic
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

    public function getProjectRootDir()
    {
        return $this->config->getProjectRootDir();
    }
}

