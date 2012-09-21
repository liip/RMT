<?php

namespace Liip\RD\Prerequisite;


class DisplayLastChanges
{
    public function execute($context)
    {
        $context->getService('output')->writeln("<info>Here is the list of change you are going to released:</info>");
        $context->getService('output')->writeln(">>>");
        $context->getService('output')->writeln(
            $context->getService('vcs')->getAllModificationsSince(
                $context->getService('vcs')->getTagFromVersion(
                    $context->getService('version-persister')->getCurrentVersion()
                )
            )
        );
        $context->getService('output')->writeln("<<<");
    }

    public function getOptions()
    {
        return array();
    }
}
