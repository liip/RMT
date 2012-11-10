<?php

namespace Liip\RD\Changelog\Formatter;

class SimpleChangelogFormatter
{
    public function updateExistingLines($lines, $version, $comment, $options)
    {
        $date = $this->getFormattedDate();
        array_splice($lines, 0, 0, array("$date  $version  $comment"));

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
