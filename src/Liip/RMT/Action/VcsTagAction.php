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

use Liip\RMT\Context;

/**
 * Create a tag with the new version number
 */
class VcsTagAction extends BaseAction
{
    public function execute()
    {
        Context::get('vcs')->createTag(
            Context::get('vcs')->getTagFromVersion(
                Context::getParam('new-version')
            )
        );
        $this->confirmSuccess();
    }
}

