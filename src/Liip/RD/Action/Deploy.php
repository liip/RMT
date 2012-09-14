<?php
namespace Liip\RD\PostReleaseAction;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Liip\RD\PostReleaseAction\BasePosttReleaseAction;

class Deploy extends BasePreReleaseAction
{
    public function execute(InputInterface $input, OutputInterface $output, VCSInterface $vcs, $lastVersion)
    {
        // Auto deploy if require
        if ( isset($options['and-deploy-to']) ){
            $output->logInfo($messages);
            $this->runTask('git:deploy', array($options['and-deploy-to']));
            return;
        }
        else {
            $messages[] = "";
            $messages[] = "You can now deploy this version with the command:";
            $messages[] = "   > symfony git:deploy [server] --ver=$newVersion";
        }
    }
}
