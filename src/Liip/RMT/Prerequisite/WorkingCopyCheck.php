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
 * Ensure VCS working copy is clean
 */
class WorkingCopyCheck extends BaseAction
{
    /**
     * Exception code when working copy is not clean.
     *
     * @var int
     */
    const EXCEPTION_CODE = 412;

    public $ignoreCheckOptionName = 'ignore-check';

    public function __construct($options = [])
    {
        parent::__construct(array_merge(['allow-ignore' => false], $options));
    }

    public function getTitle()
    {
        return 'Check that your working copy is clean';
    }

    public function execute()
    {
        // Allow to be skipped when explicitly activated from the config
        if ($this->options['allow-ignore'] && Context::get('information-collector')->getValueFor($this->ignoreCheckOptionName)) {
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
        if ($this->options['allow-ignore']) {
            return [
                new InformationRequest($this->ignoreCheckOptionName, array(
                    'description' => 'Do not process the check for clean VCS working copy',
                    'type' => 'confirmation',
                    'interactive' => false,
                ))
            ];
        }

        return [];
    }
}
