<?php

namespace Liip\RD\Prerequisites;


class DisplayLastChanges
{
    public function execute($context)
    {
        $context->getService('output')->writeln("Here is the list of change you are going to released:");
        $context->getService('output')->writeln(">>>");
        $context->getService('output')->writeln(
            $context->getService('vcs')->getAllModificationsSince(
                $context->getService('version-persister')->getCurrentVersionTag()
            )
        );
        $context->getService('output')->writeln("<<<");
    }

    public function getOptions()
    {
        return array();
    }
}
