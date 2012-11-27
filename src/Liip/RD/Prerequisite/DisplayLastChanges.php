<?php

namespace Liip\RD\Prerequisite;

use Liip\RD\Context;

class DisplayLastChanges extends BasePrerequisite
{
    public function getTitle()
    {
        return "Here is the list of changes you are going to released";
    }

    public function execute()
    {
        Context::getInstance()->getService('output')->writeln('');
        Context::getInstance()->getService('output')->writeln(
            Context::getInstance()->getService('vcs')->getAllModificationsSince(
                Context::getInstance()->getService('version-persister')->getCurrentVersionTag()
            )
        );
    }
}
