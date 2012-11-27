<?php

namespace Liip\RD\Action;

use Liip\RD\Changelog\ChangelogManager;

class ChangelogUpdateAction extends BaseAction
{
    protected $options;

    public function __construct($context, $options)
    {
        $this->options = $options;
    }

    public function execute($context)
    {
        if (isset($this->options['dump-commits']) && $this->options['dump-commits']===true) {
            $extraLines = $context->getService('vcs')->getAllModificationsSince(
                $context->getService('version-persister')->getCurrentVersionTag(),
                false
            );
            $this->options['extra-lines'] = $extraLines;
            unset($this->options['dump-commits']);
        }

        $manager = new ChangelogManager('CHANGELOG', 'semantic');
        $manager->update(
            $context->getParam('new-version'),
            $context->getService('information-collector')->getValueFor('comment'),
            array_merge(
                array('type' => $context->getService('information-collector')->getValueFor('type', null)),
                $this->options
            )
        );
        $this->confirmSuccess($context);
    }

    public function getInformationRequests()
    {
        return array('comment');
    }

}

