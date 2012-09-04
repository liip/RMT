<?php

namespace Liip\RD\Version\Persister;

use Liip\RD\Version\Persister\PersisterInterface;

class ChangelogPersister implements PersisterInterface
{
    public function getCurrentVersion()
    {
        return '1.0';
        // TODO: Implement getCurrentVersion() method.
    }

    public function save($versionNumber, $options)
    {
        // Ask user about comment
        // TODO: Implement save() method.
    }

    public function getUserQuestions()
    {
        // TODO: Implement getUserQuestions() method.
    }
}

