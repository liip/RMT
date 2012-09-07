<?php

namespace Liip\RD\Version\Persister;

use Liip\RD\Version\Persister\PersisterInterface;
use Liip\RD\UserQuestion\SimpleQuestion;
use Liip\RD\Context;

class ChangelogPersister implements PersisterInterface
{
    protected $filePath;
    protected $context;

    public function __construct(Context $context, $options = array()){
        if (!array_key_exists('location', $options)) {
            throw new \Exception('location of the changelog should be defined');
        }
        $this->filePath = $context->getProjectRootDir().'/' . $options['location'];
        if (!file_exists($this->filePath)){
            throw new \Exception("Invalid changelog location: $this->filePath");
        }
        $this->context = $context;
        $this->registerUserQuestions();
    }

    public function getCurrentVersion()
    {
        $changelog = file_get_contents($this->filePath);
        preg_match('#\s+\d+/\d+/\d+\s\d+:\d+\s\s([^\s]+)#', $changelog, $match);
        $version = $match[1];
        // TODO: do we need any check
        //if ( ! preg_match('#^\d+\.\d+$#', $version) ){
        //    throw new \Exception('Invalid format of the CHANGELOG file');
        //}
        return $version;
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
}

