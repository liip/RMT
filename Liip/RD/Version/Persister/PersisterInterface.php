<?php

namespace Liip\RD\Version\Persister;

interface PersisterInterface
{
    public function getCurrentVersion();
    public function save($versionNumber);
    public function registerUserQuestions();
}

