<?php

namespace Liip\RMT\Changelog\Formatter;

/**
 * Class SemanticChangelogFormatter
 *
 * Format a changelog file in a sementic style. Here is an example:
 *
 *  VERSION 1  MAJOR TITLE
 *  ======================
 *
 *    Version 1.1 - Minor Title
 *       08/11/1980 12:34  1.1.1  patch comment
 *          ada96f3 commit msg
 *          2eb6fae commit msg
 *       08/11/1980 03:56  1.1.0  initial release'
 *          2eb6fae commit msg
 *
 *    Version 1.0 - Minor Title
 *       08/11/1980 03:56  1.0.0  initial release'
 *          2eb6fae commit msg
 *
 *
 *  VERSION 0  BETA
 *  ===============
 *
 *    Version 0.9 - Minor Title
 *       08/11/1980 12:34  0.9.1  patch comment
 *          ada96f3 commit msg
 *          2eb6fae commit msg
 *       08/11/1980 03:56  0.9.0  initial release'
 *          2eb6fae commit msg
 *
 *
 * @package Liip\RMT\Changelog\Formatter
 */
class SemanticChangelogFormatter
{
    public function updateExistingLines($lines, $version, $comment, $options)
    {
        if (!isset($options['type'])){
            throw new \InvalidArgumentException("Option [type] in mandatory");
        }
        $type = $options['type'];
        if (!in_array($type, array('patch', 'minor', 'major'))) {
            throw new \InvalidArgumentException("Invalid type [$type]");
        }

        // Specific case for new Changelog file. We always have to write down a major
        if (count($lines) == 0){
            $type = 'major';
        }

        // Insert the new lines
        array_splice($lines, $this->findPositionToInsert($lines, $type), 0, $this->getNewLines($type, $version, $comment));

        // Insert extra lines (like commits details)
        if (isset($options['extra-lines'])) {
            foreach($options['extra-lines'] as $pos => $line) {
                $options['extra-lines'][$pos] = '         '.$line;
            }
            array_splice($lines, $this->findPositionToInsert($lines, 'patch')+1, 0, $options['extra-lines']);
        }
        return $lines;
    }

    /**
     * Return the new formatted lines for the given variables
     *
     * @param $type string     The version type, could be major, minor, patch
     * @param $version string  The new version number
     * @param $comment string  The user comment
     * @return array           An array of new lines
     */
    protected function getNewLines($type, $version, $comment)
    {
        list($major, $minor, $patch) = explode('.', $version);
        if ($type=='major') {
            $title = "version $major  $comment";
            return array_merge(
                array(
                    '',
                    strtoupper($title),
                    str_pad('', strlen($title), '=')
                ),
                $this->getNewLines('minor', $version, $comment)
            );
        }
        elseif ($type=='minor') {
            return array_merge(
                array(
                    '',
                    "   Version $major.$minor - $comment"
                ),
                $this->getNewLines('patch', $version, 'initial release')
            );
        }
        else { //patch
            $date = $this->getFormattedDate();
            return array(
                "      $date  $version  $comment"
            );
        }
    }

    /**
     * Return the position where to insert new lines according to the type of insertion
     *
     * @param $lines  array     Existing lines
     * @param $type   string    Release type
     * @return int              The position where to insert
     */
    protected function findPositionToInsert($lines, $type)
    {
        // Major are always inserted at the top
        if ($type=='major') {
            return 0;
        }

        // Minor must be inserted one line above the first major section
        if ($type=='minor') {
            foreach ($lines as $pos => $line){
                if (preg_match('/^=======/', $line)){
                    return $pos+1;
                }
            }
        }

        // Patch should go directly after the first minor
        if ($type=='patch') {
            foreach ($lines as $pos => $line){
                if (preg_match('/Version\s\d+\.\d+\s\-/', $line)){
                    return $pos+1;
                }
            }
        }

        throw new \Liip\RMT\Exception("Invalid changelog formatting");
    }

    protected function getFormattedDate()
    {
        return date('d/m/Y H:i');
    }

    public function getLastVersionRegex()
    {
        return '#\s+\d+/\d+/\d+\s\d+:\d+\s+([^\s]+)#';
    }
}

