<?php

namespace Liip\RMT\Prerequisite;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Liip\RMT\VCS\VCSInterface;
use Liip\RMT\Context;
use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Action\BaseAction;

/**
 * Ensure VCS working copy is clean
 */
class WorkingCopyCheck extends BaseAction {

    /**
     * Exception code when working copy is not clean.
     * 
     * @var int
     */
    const EXCEPTION_CODE = 412;
    
    public $ignoreCheckOptionName = 'ignore-check';

    public function getTitle()
    {
        return 'Check that your working copy is clean';
    }

    public function execute()
    {
        if (Context::get('information-collector')->getValueFor($this->ignoreCheckOptionName)) {
            Context::get('output')->writeln('<error>requested to be ignored</error>');
            return;
        }

        $modCount = count(Context::get('vcs')->getLocalModifications());
        if ($modCount > 0) {
            throw new \Exception(
                'Your working directory contains ' . $modCount . ' local modifications, use --'
                . $this->ignoreCheckOptionName.' option to bypass this check',
                self::EXCEPTION_CODE
            );
        }
        
        $this->confirmSuccess();
    }

    public function getInformationRequests()
    {
        return array(
            new InformationRequest($this->ignoreCheckOptionName, array(
                'description' => 'Do not process the check for clean working copy',
                'type' => 'confirmation',
                'interactive' => false
            ))
        );
    }
}

