<?php

namespace Liip\RD\Action;

class ChangelogUpdateAction extends BaseAction
{
    public function execute($context)
    {
        $this->confirmSuccess($context);
    }

    public function getInformationRequests()
    {
        return array('comment');
    }

}

