<?php

use Liip\RD\Information\InformationRequest;

class UpdateApplicationVersionCurrentVersion extends \Liip\RD\Action\BaseAction
{
    public function getTitle() {
        return "Application version update";
    }

    public function execute($context)
    {
        // Output for devs
        $newVersion = $context->getParam('new-version');
        $appFile = realpath(__DIR__.'/../src/Liip/RD/Application.php');
        $context->getService('output')->write("New version [<yellow>$newVersion</yellow>] udpated into $appFile: ");

        // Update the application file
        $fileContent = file_get_contents($appFile);
        $fileContent = preg_replace('/(.*define.*RMT_VERSION.*)/', "define('RMT_VERSION', '$newVersion');", $fileContent);
        file_put_contents($appFile, $fileContent);

        $this->confirmSuccess($context);
    }

}
