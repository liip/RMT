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

use Liip\RMT\Action\BaseAction;
use Liip\RMT\Context;
use Liip\RMT\Information\InformationRequest;
use Symfony\Component\Process\Process;

/**
 * Uses `composer audit` to see if composer.lock contains insecure versions - needs composer installed globally
 */
class ComposerAudit extends BaseAction
{
    const SKIP_OPTION = 'skip-composer-audit';

    public function execute()
    {
        // Handle the skip option
        if (Context::get('information-collector')->getValueFor(self::SKIP_OPTION)) {
            Context::get('output')->writeln('<error>composer audit skipped</error>');

            return;
        }

        Context::get('output')->writeln('<comment>running composer audit</comment>');

        // Run the actual security check
        $process = new Process(['composer', 'audit', '--format', 'json']);
        $process->run();

        $report = json_decode($process->getOutput(), true);

        if ($process->isSuccessful() && count($report['advisories']) === 0 && count($report['abandoned']) === 0) {
            $this->confirmSuccess();
            return;
        }

        if ($report === null) {
            throw new \RuntimeException('Error while trying to execute `composer audit` command. Are you sure the binary is installed globally in your system and you have at least composer version 2.4?');
        }

        foreach ($report['advisories'] as $package => $alert) {
            Context::get('output')->writeln("<options=bold>{$package}</options=bold> has security reports");
            foreach ($alert as $data) {
                Context::get('output')->writeln('');
                Context::get('output')->writeln($data['advisoryId']);
                Context::get('output')->writeln($data['title']);
                Context::get('output')->writeln('');
            }
        }
        foreach ($report['abandoned'] as $package => $alert) {
            Context::get('output')->writeln("<options=bold>{$package}</options=bold> is abandoned");
        }

        // throw exception to have check fail
        throw new \Exception(
            'composer.lock contains insecure packages (you can force a release with option --'.self::SKIP_OPTION.')'
        );
    }

    public function getInformationRequests(): array
    {
        return array(
            new InformationRequest(
                self::SKIP_OPTION,
                array(
                    'description' => 'Do not run composer security check before the release',
                    'type' => 'confirmation',
                    'interactive' => false,
                )
            ),
        );
    }
}
