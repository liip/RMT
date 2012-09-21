<?php

namespace Liip\RD\Action;

class VcsTagAction extends BaseAction
{
    public function execute($context)
    {
        $context->getService('vcs')->createTag(
            $context->getService('vcs')->getTagFromVersion(
                $context->getParam('new-version')
            )
        );
    }

}
