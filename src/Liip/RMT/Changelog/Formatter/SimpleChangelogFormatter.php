<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Changelog\Formatter;

class SimpleChangelogFormatter
{
    public function updateExistingLines($lines, $version, $comment, $options)
    {
        $date = $this->getFormattedDate();
        array_splice($lines, 0, 0, array("$date  $version  $comment"));

        if (isset($options['extra-lines'])) {
            array_splice($lines, 1, 0, $options['extra-lines']);
        }

        return $lines;
    }

    protected function getFormattedDate()
    {
        return date('d/m/Y H:i');
    }

    public function getLastVersionRegex()
    {
        return '#\d+/\d+/\d+\s\d+:\d+\s\s([^\s]+)#';
    }
}
