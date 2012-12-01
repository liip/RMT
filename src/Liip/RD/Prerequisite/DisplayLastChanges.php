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
        Context::get('output')->writeln('');
        Context::get('output')->writeln(
            Context::get('vcs')->getAllModificationsSince(
                Context::get('version-persister')->getCurrentVersionTag()
            )
        );
    }
}
