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
use SensioLabs\Security\SecurityChecker;

/**
 * uses https://security.sensiolabs.org/ to see if composer.lock contains insecure versions
 */
class ComposerSecurityCheck extends BaseAction
{
    const SKIP_OPTION = 'skip-composer-security-check';

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function execute()
    {
        // Handle the skip option
        if (Context::get('information-collector')->getValueFor(self::SKIP_OPTION)) {
            Context::get('output')->writeln('<error>composer security check skipped</error>');

            return;
        }

        // Run the validation and live output with the standard output class
        Context::get('output')->write("<comment>running composer security check</comment>\n\n");

        $checker = new SecurityChecker();
        $alerts = $checker->check('composer.lock');

        if (count($alerts) == 0) {
            $this->confirmSuccess();

            return;
        }

        foreach ($alerts as $package => $alert) {

            Context::get("output")->writeln("<options=bold>{$package}</options=bold> {$alert['version']}");
            foreach ($alert['advisories'] as $data) {
                Context::get("output")->writeln("");
                Context::get("output")->writeln($data['title']);
                Context::get("output")->writeln("");
                Context::get("output")->writeln($data['link']);
                Context::get("output")->writeln("");
            }
        }

        throw new \Exception(
            'composer.lock contains insecure packages (you can force a release with option --'.self::SKIP_OPTION.')'
        );
    }

    public function getInformationRequests()
    {
        return array(
            new InformationRequest(
                self::SKIP_OPTION,
                array(
                    'description' => 'Do not run composer security check before the release',
                    'type' => 'confirmation',
                    'interactive' => false
                )
            )
        );
    }
}
