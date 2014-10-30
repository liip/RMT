<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Liip\RMT\Context;

/**
 * Class UpdateApplicationVersionCurrentVersion
 *
 * Custom pre-release action for updating the version number in the application
 */
class UpdateApplicationVersionCurrentVersion extends \Liip\RMT\Action\BaseAction
{
    public function getTitle()
    {
        return "Application version update";
    }

    public function execute()
    {
        // Output for devs
        $newVersion = Context::getParam('new-version');
        $appFile = realpath(__DIR__.'/../src/Liip/RMT/Application.php');
        Context::get('output')->write("New version [<yellow>$newVersion</yellow>] updated into $appFile: ");

        // Update the application file
        $fileContent = file_get_contents($appFile);
        $fileContent = preg_replace('/(.*define.*RMT_VERSION.*)/', "define('RMT_VERSION', '$newVersion');", $fileContent);
        file_put_contents($appFile, $fileContent);

        $this->confirmSuccess();
    }
}
