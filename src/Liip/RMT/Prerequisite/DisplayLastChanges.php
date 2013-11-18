<?php

namespace Liip\RMT\Prerequisite;

use Liip\RMT\Context;
use Liip\RMT\Action\BaseAction;

class DisplayLastChanges extends BaseAction
{
    public function getTitle()
    {
        return "Here is the list of changes you are going to release";
    }

    public function execute()
    {
        try {
            Context::get('output')->writeEmptyLine();
            Context::get('output')->writeln(
                Context::get('vcs')->getAllModificationsSince(
                    Context::get('version-persister')->getCurrentVersionTag()
                )
            );
        }
        catch (\Exception $e){
            Context::get('output')->writeln('<error>No modification found: '.$e->getMessage().'</error>');
        }
    }
}

