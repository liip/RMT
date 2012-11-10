<?php

namespace Liip\RD\Action;

use Liip\RD\Information\InformationRequest;

class VcsPublishAction extends BaseAction
{
    public function execute($context)
    {
        $context->getService('vcs')->publishTag(
            $context->getService('version-persister')->getTagFromVersion(
                $context->getParam('new-version')
            )
        );
        $context->getService('vcs')->publishChanges();
        $this->confirmSuccess($context);
    }

    public function getInformationRequests()
    {
        return array(
            new InformationRequest('auto-publish', array(
                'description' => 'Automatically publish the change',
                'type' => 'boolean',
                'interactive' => true,
                'default' => false
            ))
        );
    }
}
