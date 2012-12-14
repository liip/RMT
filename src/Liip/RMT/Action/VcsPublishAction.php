<?php

namespace Liip\RMT\Action;

use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Context;

/**
 * Push current branch and tag to version control
 */
class VcsPublishAction extends BaseAction
{
    public function execute()
    {
        if (Context::get('information-collector')->getValueFor('confirm-publish') !== 'y'){
            Context::get('output')->writeln('<error>requested to be ignored</error>');
            return;
        }

        Context::get('vcs')->publishChanges();
        Context::get('vcs')->publishTag(
            Context::get('version-persister')->getTagFromVersion(
                Context::getParam('new-version')
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

