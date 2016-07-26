<?php

namespace Liip\RMT\Changelog\Formatter;

/**
 * @author Titouan Galopin
 */
class CustomChangelogFormatter
{
    public function updateExistingLines($lines, $version, $comment, $options)
    {
        $newLines = [];
        $newLines[] = '* '.$version.' ('.date('Y-m-d').')';
        
        if (isset($options['extra-lines'])) {
            $newLines[] = '';

            foreach ($options['extra-lines'] as $extraLine) {
                $newLines[] = ' * '.$extraLine;
            }
        }

        $newLines[] = '';

        foreach ($lines as $line) {
            $newLines[] = $line;
        }

        return $newLines;
    }
}
