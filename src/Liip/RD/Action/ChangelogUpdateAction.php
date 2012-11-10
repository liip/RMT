<?php

namespace Liip\RD\Action;

use Liip\RD\Changelog\ChangelogManager;

class ChangelogUpdateAction extends BaseAction
{
    public function execute($context)
    {
        $manager = new ChangelogManager('CHANGELOG', 'semantic');
        $manager->update(
            $context->getParam('new-version'),
            $context->getService('information-collector')->getValueFor('comment'),
            array('type' => $context->getService('information-collector')->getValueFor('type', null))
        );
        $this->confirmSuccess($context);
    }

    public function getInformationRequests()
    {
        return array('comment');
    }

}

