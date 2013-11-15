<?php

namespace Liip\RMT\Prerequisite;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Process\Process;

use Liip\RMT\VCS\VCSInterface;
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
            'expected_exit_code' => 0
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
        $command = $this->options['command'];
        Context::get('output')->write("<comment>$command</comment>\n\n");
        $process = new Process($command);
        $process->run(function ($type, $buffer) {
            Context::get('output')->write($buffer);
        });

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
                'interactive' => false
            ))
        );
    }
}

