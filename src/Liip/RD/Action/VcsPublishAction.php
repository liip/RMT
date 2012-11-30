<?php

namespace Liip\RD\Action;

use Liip\RD\Information\InformationRequest;
use Liip\RD\Context;

class VcsPublishAction extends BaseAction
{
    public function execute()
    {
        if (Context::getInstance()->getService('information-collector')->getValueFor('confirm-publish') !== 'y'){
            Context::getInstance()->getService('output')->writeln('<error>requested to be ignored</error>');
            return;
        }

        Context::getInstance()->getService('vcs')->publishChanges();
        Context::getInstance()->getService('vcs')->publishTag(
            Context::getInstance()->getService('version-persister')->getTagFromVersion(
                Context::getInstance()->getParam('new-version')
            )
        );

        $this->confirmSuccess();
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
