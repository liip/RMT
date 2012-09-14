<?php

namespace Liip\RD\Version\Persister;

use Liip\RD\Version\Persister\PersisterInterface;
use Liip\RD\UserQuestion\SimpleQuestion;
use Liip\RD\Context;

class ChangelogPersister implements PersisterInterface
{
    protected $filePath;
    protected $context;

    public function __construct($context, $options = array())
    {
        if (!array_key_exists('location', $options)) {
            $options['location'] = 'CHANGELOG';
        }
        $this->filePath = $context->getParam('project-root').'/' . $options['location'];
        if (!file_exists($this->filePath)){
            throw new \Exception("Invalid changelog location: $this->filePath, if it's the first time you use RD use the --init parameter to create it");
        }
        $this->context = $context;
        $this->registerUserQuestions();
    }

    public function getCurrentVersion()
    {
        $changelog = file_get_contents($this->filePath);
        preg_match('#\s+\d+/\d+/\d+\s\d+:\d+\s\s([^\s]+)#', $changelog, $match);
        if (isset($match[1])){
            return $match[1];
        }
        throw new \Liip\RD\Exception("There is a format error in the CHANGELOG file");
    }

    public function save($versionNumber)
    {
        $changelog = file($this->filePath, FILE_IGNORE_NEW_LINES);
        $date = date('d/m/Y H:i');
        $commentQuestion = $this->context->getUserQuestionByTopic('comment');
        $comment = $commentQuestion->getAnswer();

        array_splice($changelog, 2, 0, array("    $date  $versionNumber  $comment"));
        file_put_contents($this->filePath, implode("\n", $changelog));
    }

    public function registerUserQuestions()
    {
        $question = new SimpleQuestion('Please insert a comment');
        $this->context->addUserQuestion('comment', $question);
    }

    public function init()
    {
        // TODO: Implement init() method.
    }
}

