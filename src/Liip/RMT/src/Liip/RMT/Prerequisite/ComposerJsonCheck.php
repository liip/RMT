<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2014, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Prerequisite;

use Liip\RMT\Context;
use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Action\BaseAction;

/**
 * Run a test suite and interrupt the process if the return code is not good
 */
class ComposerJsonCheck extends BaseAction
{
    const SKIP_OPTION = 'skip-composer-json-check';

    public function __construct($options)
    {
        $this->options = array_merge(array(
            'composer' => 'php composer.phar',
        ), $options);
    }

    public function execute()
    {
        // Handle the skip option
        if (Context::get('information-collector')->getValueFor(self::SKIP_OPTION)) {
            Context::get('output')->writeln('<error>composer.json validation skipped</error>');

            return;
        }

        // Run the validation and live output with the standard output class
        $process = $this->executeCommandInProcess($this->options['composer'] . '  validate');

        // Break up if the result is not good
        if ($process->getExitCode() !== 0) {
            throw new \Exception('composer.json invalid (you can force a release with option --'.self::SKIP_OPTION.')');
        }

        $this->confirmSuccess();
    }

    public function getInformationRequests()
    {
        return array(
            new InformationRequest(self::SKIP_OPTION, array(
                'description' => 'Do not validate composer.json before the release',
                'type' => 'confirmation',
                'interactive' => false,
            )),
        );
    }
}
