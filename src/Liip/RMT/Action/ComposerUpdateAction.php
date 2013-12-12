<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Action;

use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Context;

/**
 * Update the version in composer.json
 */
class ComposerUpdateAction extends \Liip\RMT\Action\BaseAction
{
    public function execute()
    {
        $newVersion = Context::getParam('new-version');
        $composerFile = Context::getParam('project-root').'/composer.json';
        if (!file_exists($composerFile)) {
            throw new \Liip\RMT\Exception("Impossible to file the composer file ($composerFile)");
        }
        $fileContent = file_get_contents($composerFile);
        $fileContent = preg_replace('/("version":[^,]*,)/', '"version": "'.$newVersion.'",', $fileContent);
        file_put_contents($composerFile, $fileContent);
        $this->confirmSuccess();
    }
}

