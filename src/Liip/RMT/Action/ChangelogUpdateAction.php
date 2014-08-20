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

use Liip\RMT\Changelog\ChangelogManager;
use Liip\RMT\Context;
use Liip\RMT\Exception\NoReleaseFoundException;

/**
 * Update the changelog file
 */
class ChangelogUpdateAction extends BaseAction
{

    public function __construct($options)
    {
        $this->options = array_merge(array(
            'dump-commits' => false,
            'exclude-merge-commits' => false,
            'format' => 'simple',
            'file' => 'CHANGELOG'
        ), $options);
    }

    public function execute()
    {
        // Handle the commits dump
        if ($this->options['dump-commits'] == true) {
            try {
                $extraLines = Context::get('vcs')->getAllModificationsSince(
                    Context::get('version-persister')->getCurrentVersionTag(),
                    false,
                    $this->options['exclude-merge-commits']
                );
                $this->options['extra-lines'] = $extraLines;
            } catch (NoReleaseFoundException $e) {
                Context::get('output')->writeln("<error>No commits dumped as this is the first release</error>");
            }
            unset($this->options['dump-commits']);
        }

        $manager = new ChangelogManager($this->options['file'], $this->options['format']);
        $manager->update(
            Context::getParam('new-version'),
            Context::get('information-collector')->getValueFor('comment'),
            array_merge(
                array('type' => Context::get('information-collector')->getValueFor('type', null)),
                $this->options
            )
        );
        $this->confirmSuccess();
    }

    public function getInformationRequests()
    {
        return array('comment');
    }
}
