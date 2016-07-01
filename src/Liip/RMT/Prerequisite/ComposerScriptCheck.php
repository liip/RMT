<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2016, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Prerequisite;

use Liip\RMT\Context;
use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Action\BaseAction;

/**
 * Run a set of Composer scripts and interrupt the process if the return code is not good
 */
class ComposerScriptCheck extends BaseAction
{
    const SKIP_OPTION = 'skip-composer-script-check';

    public function __construct($options)
    {
        if (!isset($options['scripts']) || count($options['scripts']) == 0) {
            $exceptionMessage = 'No Composer scripts provided (you can force '
                . 'a release with option --'.self::SKIP_OPTION.')';
            throw new \Exception($exceptionMessage);
        }

        $this->options = array_merge(array(
            'composer' => 'php composer.phar',
            'scripts' => array(),
            'expected_exit_code' => 0
        ), $options);
    }

    public function getTitle()
    {
        return 'Running the configured Composer scripts';
    }

    public function execute()
    {
        // Handle the skip option
        if (Context::get('information-collector')->getValueFor(self::SKIP_OPTION)) {
            Context::get('output')->writeln(
                '<error>Composer scripts execution skipped</error>'
            );

            return;
        }

        $failedScripts = array_filter(array_map(function($script) {
            // Run the validation and live output with the standard output class
            $process = $this->executeCommandInProcess($this->options['composer'] . ' ' . $script);
            if ($process->getExitCode() !== (int) $this->options['expected_exit_code']) {
                return array(
                    'script' => $process->getCommandLine(),
                    'exit_code' => $process->getExitCode()
                );
            }
        }, $this->options['scripts']));

        if (count($failedScripts) > 0) {
            $failedScript = $failedScripts[0]['script'];
            $exceptionMessage = 'The Composer script ' . $failedScript
                . ' failed (you can force a release with option --'.self::SKIP_OPTION.')';

            if (count($failedScripts) > 1) {
                $failedScripts = array_map(function($failedScript) {
                    return $failedScript['script'];
                }, $failedScripts);

                $failedScriptsListing = implode(', ', $failedScripts);
                $exceptionMessage = 'The Composer scripts ' . $failedScriptsListing
                    . ' failed (you can force a release with option --'.self::SKIP_OPTION.')';
            }

            throw new \Exception($exceptionMessage);
        }

        $this->confirmSuccess();
    }

    public function getInformationRequests()
    {
        return array(
            new InformationRequest(self::SKIP_OPTION, array(
                'description' => 'Do not run the Composer scripts before the release',
                'type' => 'confirmation',
                'interactive' => false,
            )),
        );
    }
}
