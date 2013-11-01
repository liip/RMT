<?php

namespace Liip\RMT\Changelog\Formatter;

/**
 * Adding the version heading at the top of the CHANGELOG file.
 *
 * This is useful when you constantly record relevant changes and want a new
 * heading so that people see in which version that changed.
 *
 * An example file:
 *
 * Changelog
 * =========
 *
 * * **2013-11-01**: A changelog entry for a feature that is in no released
 *   version yet. The version header will be added right before this when
 *   the addTop formatter is used.
 *
 * 1.0.0-RC3
 * ---------
 * * **2013-10-04**: A manual changelog entry
 * * **2013-10-02**: A first entry into the changlog
 *
 * 1.0.0-beta-3
 * ------------
 * * **2013-09-23**: An older changelog entry
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
