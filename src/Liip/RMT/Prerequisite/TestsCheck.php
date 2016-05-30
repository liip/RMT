<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
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
class TestsCheck extends BaseAction
{
    const SKIP_OPTION = 'skip-testing';

    public function __construct($options)
    {
        $this->options = array_merge(array(
            'command' => 'phpunit --stop-on-failure',
            'expected_exit_code' => 0,
        ), $options);
    }

    public function execute()
    {
        // Handle the skip option
        if (Context::get('information-collector')->getValueFor(self::SKIP_OPTION)) {
            Context::get('output')->writeln('<error>tests skipped</error>');

            return;
        }

        // Run the tests and live output with the standard output class
        $process = $this->executeCommandInProcess($this->options['command']);

        // Break up if the result is not good
        if ($process->getExitCode() !== $this->options['expected_exit_code']) {
            throw new \Exception('Tests fails (you can force a release with option --'.self::SKIP_OPTION.')');
        }
    }

    public function getInformationRequests()
    {
        return array(
            new InformationRequest(self::SKIP_OPTION, array(
                'description' => 'Do not run the tests before the release',
                'type' => 'confirmation',
                'interactive' => false,
            )),
        );
    }
}
