<?php

namespace Liip\RD\Prerequisite;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Liip\RD\VCS\VCSInterface;
use Liip\RD\Context;
use Liip\RD\Information\InformationRequest;


class WorkingCopyCheck extends BasePrerequisite {

    public $ignoreCheckOptionName = 'ignore-check';

    public function execute($context)
    {
        $context->getService('output')->write('<info>Check that your working copy is clean:</info> ');
        if ($context->getService('information-collector')->getValueFor($this->ignoreCheckOptionName))
        {
            $context->getService('output')->writeln("Check is ignored...");
            return;
        }
        if (count($modif = $context->getService('vcs')->getLocalModifications()) > 0){
            throw new \Exception('Your working directory contain local modifications, use --'.$this->ignoreCheckOptionName.' option to bypass this check');
        }
        $context->getService('output')->writeln("Check OK !");
    }

    public function getInformationRequests()
    {
        return array(
            new InformationRequest($this->ignoreCheckOptionName, array(
                'description' => 'Do not process the check for clean working copy',
                'type' => 'boolean'
            ))
        );
    }
}