<?php

namespace Liip\RMT\Action;

use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Context;

/**
 * Push current branch and tag to version control
 */
class VcsPublishAction extends BaseAction
{

    public function __construct($options = array())
    {
        $this->options = array_merge(array(
            'ask-confirmation' => true,
            'remote-name' => null,
            'ask-remote-name' => false
        ), $options);
    }

    public function execute()
    {
        if ($this->options['ask-confirmation'] && Context::get('information-collector')->getValueFor('confirm-publish') !== 'y'){
            Context::get('output')->writeln('<error>requested to be ignored</error>');
            return;
        }

        Context::get('vcs')->publishChanges($this->getRemote());
        Context::get('vcs')->publishTag(
            Context::get('version-persister')->getTagFromVersion(
                Context::getParam('new-version')
            ),
            $this->getRemote()
        );

        $this->confirmSuccess();
    }

    public function getInformationRequests()
    {
        $requests = array();
        if ($this->options['ask-confirmation']) {
            $requests[] = new InformationRequest('confirm-publish', array(
                'description' => 'Changes will be published automatically',
                'type' => 'yes-no',
                'default' => 'yes'
            ));
        }
        if ($this->options['ask-remote-name']) {
            $requests[] = new InformationRequest('remote', array(
                'description' => 'Remote to push changes',
                'type' => 'text',
                'default' => 'origin'
            ));
        }

        return $requests;
    }

    /**
     * Return the remote name where to publish or null if not defined
     *
     * @return string|null
     */
    protected function getRemote()
    {
        if ($this->options['ask-remote-name']) {
            return Context::get('information-collector')->getValueFor('remote');
        }
        if ($this->options['remote-name'] !== null) {
            return $this->options['remote-name'];
        }

        return null;
    }
}

