<?php

namespace Liip\RD\UserQuestion;

class SimpleQuestion implements UserQuestionInterface
{
    protected $text;
    protected $defaultValue;
    protected $answer;

    public function __construct($text, $default = '')
    {
        $this->text = $text;
        $this->defaultValue = $default;
    }

    public function getQuestionText()
    {
        return $this->text . "\n";
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }

    public function getAnswer()
    {
        return $this->answer;
    }
}

