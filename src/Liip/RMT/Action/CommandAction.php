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
    public function __construct($options)
    {
        $this->options = array_merge(array(
            'cmd' => null,
            'live_output' => true,
            'stop_on_error' => true
        ), $options);

        if ($this->options['cmd'] == null) {
            throw new \RuntimeException('Missing [cmd] option');
        }
    }

    public function execute()
    {
        $command = $this->options['cmd'];
        Context::get('output')->write("<comment>$command</comment>\n\n");

        // Prepare a callback for live output
        $callback = null;
        if ($this->options['live_output'] == true) {
            $callback = function ($type, $buffer) {
                $decorator = array('','');
                if ($type == Process::ERR) {
                    $decorator = array('<error>','</error>');
                }
                Context::get('output')->write($decorator[0] . $buffer.$decorator[1]);
            };
        }

        // Run the process
        $process = new Process($command);
        $process->run($callback);

        // Break up if the result is not good
        if ($this->options['stop_on_error'] && $process->getExitCode() !== 0) {
            throw new \RuntimeException("Command [$command] exit with code " . $process->getExitCode());
        }
    }
}
