<?php

namespace Liip\RD\Action;

class VcsPublishAction extends BaseAction
{
    public function execute($context)
    {
        $context->getService('vcs')->publishTag(
            $context->getService('vcs')->getTagFromVersion(
                $context->getParam('new-version')
            )
        );
        $context->getService('vcs')->publishChanges();
        $this->confirmSuccess($context);
    }
}
