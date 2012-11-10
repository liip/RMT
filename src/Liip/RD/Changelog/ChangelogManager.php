<?php

namespace Liip\RD\Changelog;

class ChangelogManager {
    
    protected $filePath;
    protected $formatter;

    public function __construct($filePath, $format)
    {
        // Store the filePath
        if (!file_exists($filePath)){
            throw new \Liip\RD\Exception("Invalid changelog location: $filePath");
        }
        $this->filePath = $filePath;

        // Store the formatter
        $this->format = $format;
        $formatterClass = 'Liip\\RD\\Changelog\\Formatter\\'.ucfirst($format).'ChangelogFormatter';
        if (!class_exists($formatterClass)){
            throw new \Exception("There is no formatter for [$format]");
        }
        $this->formatter = new $formatterClass();
    }

    public function update($version, $comment, $options = array())
    {
        $lines = file($this->filePath, FILE_IGNORE_NEW_LINES);
        $lines = $this->formatter->updateExistingLines($lines, $version, $comment, $options);
        file_put_contents($this->filePath, implode("\n", $lines));
    }

    public function getCurrentVersion()
    {
        $changelog = file_get_contents($this->filePath);
        $result = preg_match($this->formatter->getLastVersionRegex(), $changelog, $match);
        if ($result === 1){
            return $match[1];
        }
        throw new \Liip\RD\Exception\NoReleaseFoundException (
            "There is a format error in the CHANGELOG file, impossible to read the last version number"
        );
    }
}
