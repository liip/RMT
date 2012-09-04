<?php
namespace Liip\RD\PreReleaseAction;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Liip\RD\PreReleaseAction\BasePreReleaseAction;
use Liip\RD\VCS\VCSInterface;
use Liip\RD\Context;


class DisplayLastChanges extends BasePreReleaseAction
{
    public function execute(Context $context)
    {
        // Display list of changes
        $context->getOutput()->writeln("Here is the list of change you are going to released:");
        $context->getOutput()->writeln(">>>");
        $context->getVCS()->getChangesForNewVersion($context->getCurrentVersion());
        $context->getOutput()->writeln("<<<");
    }
}
