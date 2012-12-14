<?php

namespace Liip\RMT\Prerequisite;

use Liip\RMT\Context;
use Liip\RMT\Action\BaseAction;

class DisplayLastChanges extends BaseAction
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

