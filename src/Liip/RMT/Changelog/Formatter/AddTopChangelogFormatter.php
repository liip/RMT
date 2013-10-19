<?php

namespace Liip\RMT\Changelog\Formatter;

/**
 * Adding the version heading at the top of the CHANGELOG file.
 *
 * This is useful when you constantly record relevant changes and want a new
 * heading so that people see in which version that changed.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class AddTopChangelogFormatter
{
    public function updateExistingLines($lines, $version, $comment, $options)
    {
        $pos = isset($options['insert-at']) ? $options['insert-at'] : 0;

        if (!empty($comment)) {
            array_splice($lines, $pos, 0, array($comment, ''));
        }
        if (isset($options['extra-lines'])) {
            array_splice($lines, $pos, 0, $options['extra-lines']);
        }

        array_splice($lines, $pos, 0, array($version, str_repeat('-', strlen($version)), ''));

        return $lines;
    }
}
