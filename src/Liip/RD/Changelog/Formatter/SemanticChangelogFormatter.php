<?php

namespace Liip\RD\Changelog\Formatter;

class SemanticChangelogFormatter
{
    public function updateExistingLines($lines, $type, $version, $comment)
    {
        if (!in_array($type, array('patch', 'minor', 'major'))) {
            throw new \InvalidArgumentException("Invalid type");
        }
        if ($type==='major') {
            array_splice($lines, 0, 0, $this->getNewMajorLines($version, $comment));
        }
        else if ($type==='minor') {
            array_splice($lines, 0, 4, $this->getNewMinorLines($version, $comment));
        }
        else {
            array_splice($lines, 0, 5, $this->getNewPatchLines($version, $comment));
        }
        return $lines;
    }

    protected function getNewMajorLines($version, $comment)
    {
        list($major, $minor, $patch) = explode('.', $version);
        $title = "version $major  $comment";
        return array_merge(
            array(
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

    public function getFormattedDate()
    {
        return date('d/m/Y H:i');
    }
}
