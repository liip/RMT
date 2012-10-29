<?php

namespace Liip\RD\Prerequisite;


class DisplayLastChanges extends BasePrerequisite
{
    public function getTitle()
    {
        return "Here is the list of changes you are going to released";
    }

    public function execute($context)
    {
        $context->getService('output')->writeln('');
        $context->getService('output')->writeln(
            $context->getService('vcs')->getAllModificationsSince(
                $context->getService('vcs')->getTagFromVersion(
                    $context->getService('version-persister')->getCurrentVersion()
                )
            )
        );
    }
}
