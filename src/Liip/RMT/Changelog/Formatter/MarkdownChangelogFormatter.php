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

/**
 * Class MarkdownChangelogFormatter
 *
 * Format a changelog file in a Markdown compatible style. Here is an example:
 *
 *  ## VERSION 1  MAJOR TITLE
 *
 *   * Version **1.1** - Minor Title
 *      * 1980-11-08 12:34  **1.1.1**  patch comment
 *          * ada96f3 commit msg
 *          * 2eb6fae commit msg
 *      * 1980-11-08 03:56  **1.1.0**  initial release'
 *          * 2eb6fae commit msg
 *
 *   * Version *1.0* - Minor Title
 *      * 1980-11-08 03:56  **1.0.0**  initial release'
 *          * 2eb6fae commit msg
 *
 * ## VERSION 0  BETA
 *
 *  * Version **0.9** - Minor Title
 *     * 1980-11-08 12:34  **0.9.1**  patch comment
 *         * ada96f3 commit msg
 *         * 2eb6fae commit msg
 *     * 1980-11-08 03:56  **0.9.0**  initial release'
 *         * 2eb6fae commit msg
 */
class MarkdownChangelogFormatter extends SemanticChangelogFormatter
{
    /**
     * Return the new formatted lines for the given variables
     *
     * @param string $type    The version type, could be major, minor, patch
     * @param string $version The new version number
     * @param string $comment The user comment
     *
     * @return array An array of new lines
     */
    protected function getNewLines($type, $version, $comment)
    {
        list($major, $minor, $patch) = explode('.', $version);
        if ($type == 'major') {
            $title = "version $major  $comment";

            return array_merge(
                array(
                    '',
                    '## '.strtoupper($title)
                ),
                $this->getNewLines('minor', $version, $comment)
            );
        } elseif ($type == 'minor') {
            return array_merge(
                array(
                    '',
                    " * Version **$major.$minor** - $comment",
                ),
                $this->getNewLines('patch', $version, 'initial release')
            );
        } else { //patch
            $date = $this->getFormattedDate();

            return array(
                "   * $date  **$version**  $comment",
            );
        }
    }

    /**
     * Return the position where to insert new lines according to the type of insertion
     *
     * @param array  $lines Existing lines
     * @param string $type  Release type
     *
     * @return int The position where to insert
     *
     * @throws \Liip\RMT\Exception
     */
    protected function findPositionToInsert($lines, $type)
    {
        // Major are always inserted at the top
        if ($type == 'major') {
            return 0;
        }

        // Minor must be inserted one line above the first major section
        if ($type == 'minor') {
            foreach ($lines as $pos => $line) {
                if (preg_match('/^##\ +/', $line)) {
                    return $pos + 1;
                }
            }
        }

        // Patch should go directly after the first minor
        if ($type == 'patch') {
            foreach ($lines as $pos => $line) {
                if (preg_match('/\ \*\ Version\s\*\*\d+\.\d+\*\*\s\-/', $line)) {
                    return $pos + 1;
                }
            }
        }

        throw new \Liip\RMT\Exception('Invalid changelog formatting');
    }

    protected function getFormattedDate()
    {
        return date('Y-m-d H:i');
    }

    public function getLastVersionRegex()
    {
        return '#\s+\d+/\d+/\d+\s\d+:\d+\s+([^\s]+)#';
    }

    /**
     * format extra lines (such as commit details)
     * @param array $lines
     * @return array
     */
    protected function formatExtraLines($lines)
    {
        foreach ($lines as $pos => $line) {
            $lines[$pos] = '      * '.$line;
        }
        return $lines;
    }
}
