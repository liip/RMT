<?php

namespace Liip\RD\Action;

use Liip\RD\Changelog\ChangelogManager;
use Liip\RD\Context;

class ChangelogUpdateAction extends BaseAction
{
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function execute()
    {
        if (isset($this->options['dump-commits']) && $this->options['dump-commits']===true) {
            $extraLines = Context::getInstance()->getService('vcs')->getAllModificationsSince(
                Context::getInstance()->getService('version-persister')->getCurrentVersionTag(),
                false
            );
            $this->options['extra-lines'] = $extraLines;
            unset($this->options['dump-commits']);
        }

        $manager = new ChangelogManager('CHANGELOG', 'semantic');
        $manager->update(
            Context::getInstance()->getParam('new-version'),
            Context::getInstance()->getService('information-collector')->getValueFor('comment'),
            array_merge(
                array('type' => Context::getInstance()->getService('information-collector')->getValueFor('type', null)),
                $this->options
            )
        );
        $this->confirmSuccess();
    }

    public function getInformationRequests()
    {
        return array('comment');
    }

}

