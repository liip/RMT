<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Action;

use Symfony\Component\Process\Process;
use Liip\RMT\Context;

/**
 * Run a system command
 */
class CommandAction extends BaseAction
{
    protected $currentVersion;

    public function __construct($options)
    {
        parent::__construct($options);

        $this->options = array_merge(array(
            'cmd' => null,
            'live_output' => true,
            'stop_on_error' => true,
            'timeout' => 600,
        ), $options);

        if ($this->options['cmd'] == null) {
            throw new \RuntimeException('Missing [cmd] option');
        }

        $this->currentVersion = Context::get('version-persister')->getCurrentVersion();
    }

    public function execute()
    {
        $command = $this->prepareCommand($this->options['cmd']);
        Context::get('output')->write("<comment>$command</comment>\n\n");

        // Prepare a callback for live output
        $callback = null;
        if ($this->options['live_output']) {
            $callback = function ($type, $buffer) {
                $decorator = array('','');
                if ($type == Process::ERR) {
                    $decorator = array('<error>','</error>');
                }
                Context::get('output')->write($decorator[0] . $buffer.$decorator[1]);
            };
        }

        // Run the process
        $process = method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline($command) : new Process($command);

        if (null !== $timeout = $this->options['timeout']) {
            $process->setTimeout($timeout);
        }

        $process->run($callback);

        // Break up if the result is not good
        if ($this->options['stop_on_error'] && $process->getExitCode() !== 0) {
            throw new \RuntimeException("Command [$command] exit with code " . $process->getExitCode());
        }
    }

    /**
     * Prepares the command
     *
     * @param string $command
     * @return string
     */
    protected function prepareCommand($command)
    {
        if (substr_count($command, '%') < 2) {
            return $command;
        }

        preg_match_all('@%([A-Za-z0-9_]*)%@', $command, $matches);

        if (! array_key_exists(1, $matches)) {
            return $command;
        }

        $placeHolderValue = [
            'version' => $this->currentVersion,
            'new_version' => Context::getParam('new-version'),
        ];

        foreach ($matches[1] as $placeHolder) {
            if (array_key_exists($placeHolder, $placeHolderValue)) {
                $command = str_replace("%$placeHolder%", $placeHolderValue[$placeHolder], $command);
            }
        }

        return $command;
    }
}
