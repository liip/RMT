<?php

use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Context;

class UpdateApplicationVersionCurrentVersion extends \Liip\RMT\Action\BaseAction
{
    public function getTitle() {
        return "Application version update";
    }

    public function execute()
    {
        // Output for devs
        $newVersion = Context::getParam('new-version');
        $appFile = realpath(__DIR__.'/../src/Liip/RMT/Application.php');
        Context::get('output')->write("New version [<yellow>$newVersion</yellow>] udpated into $appFile: ");

        // Update the application file
        $fileContent = file_get_contents($appFile);
        $fileContent = preg_replace('/(.*define.*RMT_VERSION.*)/', "define('RMT_VERSION', '$newVersion');", $fileContent);
        file_put_contents($appFile, $fileContent);

        $this->confirmSuccess();
    }

}
