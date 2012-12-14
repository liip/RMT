<?php

namespace Liip\RMT\Changelog\Formatter;

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

        if ($type==='major') {
            array_splice($lines, 0, 0, $this->getNewMajorLines($version, $comment));
        }
        else if ($type==='minor') {
            array_splice($lines, 3, 0, $this->getNewMinorLines($version, $comment));
        }
        else {
            array_splice($lines, 5, 0, $this->getNewPatchLines($version, $comment));
        }

        if (isset($options['extra-lines'])) {
            foreach($options['extra-lines'] as $pos => $line) {
                $options['extra-lines'][$pos] = '         '.$line;
            }
            array_splice($lines, 6, 0, $options['extra-lines']);
        }
        return $lines;
    }

    protected function getNewMajorLines($version, $comment)
    {
        list($major, $minor, $patch) = explode('.', $version);
        $title = "version $major  $comment";
        return array_merge(
            array(
                '',
                strtoupper($title),
                str_pad('', strlen($title), '=')
            ),
            $this->getNewMinorLines($version, $comment)
        );
    }

    protected function getNewMinorLines($version, $comment){
        list($major, $minor, $patch) = explode('.', $version);
        return array_merge(
            array(
                '',
                "   Version $major.$minor - $comment"
            ),
            $this->getNewPatchLines($version, 'initial release')
        );
    }

    protected function getNewPatchLines($version, $comment){
        $date = $this->getFormattedDate();
        return array(
            "      $date  $version  $comment"
        );
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

