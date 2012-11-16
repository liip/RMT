<?php

namespace Liip\RD\Action;

use Liip\RD\Information\InformationRequest;

class VcsPublishAction extends BaseAction
{
    public function execute($context)
    {
        if ($context->getService('information-collector')->getValueFor('confirm-publish') !== 'y'){
            $context->getService('output')->writeln('<error>requested to be ignored</error>');
            return;
        }

        $context->getService('vcs')->publishChanges();
        $context->getService('vcs')->publishTag(
            $context->getService('version-persister')->getTagFromVersion(
                $context->getParam('new-version')
            )
        );

        $this->confirmSuccess($context);
    }

    public function getInformationRequests()
    {
        return array(
            new InformationRequest('confirm-publish', array(
                'description' => 'Changes will be published automatically',
                'type' => 'yes-no',
                'default' => 'yes'
            ))
        );
    }
}
