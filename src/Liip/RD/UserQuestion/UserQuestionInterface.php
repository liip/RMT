<?php

namespace Liip\RD\UserQuestion;

interface UserQuestionInterface
{
    public function getQuestionText();
    public function getDefaultValue();
    public function setAnswer($answer);
    public function getAnswer();
}

