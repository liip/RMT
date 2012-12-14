<?php

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

