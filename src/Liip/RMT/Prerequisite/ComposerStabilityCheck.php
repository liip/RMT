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
 * Run a test suite and interrupt the process if the return code is not good
 */
class ComposerStabilityCheck extends BaseAction
{
    const SKIP_OPTION = 'skip-composer-stability-check';

    public function __construct($options)
    {
        $this->options = array_merge(
            array(
                'stability' => 'stable'
            ),
            $options
        );
    }

    public function execute()
    {
        // Handle the skip option
        if (Context::get('information-collector')->getValueFor(self::SKIP_OPTION)) {
            Context::get('output')->writeln('<error>composer minimum-stability check skipped</error>');

            return;
        }

        // file exists?
        if (!file_exists('composer.json')) {
            Context::get('output')->writeln('<error>composer.json does not exist, skipping check</error>');

            return;
        }

        // if file is not readable, we can't perform our check
        if (!is_readable('composer.json')) {
            throw new \Exception(
                'composer.json can not be read (permissions?), (you can force a release with option --'
                . self::SKIP_OPTION.')'
            );
        }

        $contents = json_decode(file_get_contents('composer.json'), true);

        // fail if the composer config falls back to default, and this check has something else but default set
        if (!isset($contents['minimum-stability']) && $this->options['stability'] != 'stable') {
            throw new \Exception(
                'minimum-stability is not set, but RMT config requires: '
                . $this->options['stability'].' (you can force a release with option --'
                . self::SKIP_OPTION.')'
            );
        }

        // fail if stability is set and not the one expected
        if (isset($contents['minimum-stability']) && $contents['minimum-stability'] != $this->options['stability']) {
            throw new \Exception(
                'minimum-stability is set to: '
                . $contents['minimum-stability']
                . ', but RMT config requires: '
                . $this->options['stability']
                . ' (you can force a release with option --'.self::SKIP_OPTION.')'
            );
        }

        $this->confirmSuccess();
    }

    public function getInformationRequests()
    {
        return array(
            new InformationRequest(
                self::SKIP_OPTION,
                array(
                    'description' => 'Do not check composer.json for minimum-stability before the release',
                    'type' => 'confirmation',
                    'interactive' => false
                )
            )
        );
    }
}
