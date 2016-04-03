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

use Liip\RMT\Context;
use Liip\RMT\Exception;

/**
 * An updater that updates the version information stored in any kind of file.
 *
 * This file could be a configuration file (yml, json) or a package.json file
 * for instance.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class ComposerUpdateAction extends UpdateFileAction
{
    public function execute()
    {
        $composerFile = Context::getParam('project-root').'/composer.json';

        if (! file_exists($composerFile)) {
            throw new Exception(sprintf('Composer file not found (searched at %s)', $composerFile));
        }

        $this->updateFile($composerFile, '"version": "%version%"');
    }
}
