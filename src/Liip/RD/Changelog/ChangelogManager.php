<?php

namespace Liip\RD\Changelog;


class ChangelogManager {
    
    protected $filePath;

    public function __construct($filePath){
        if (!file_exists($filePath)){
            throw new \Exception("Invalid changelog location: $filePath");
        }
        $this->filePath = $filePath;
    }

    public function getCurrentVersion(){
        $changelog = file_get_contents($this->filePath);
        preg_match('#\s+\d+/\d+/\d+\s\d+:\d+\s\s([^\s]+)#', $changelog, $match);
        $version = $match[1];
        if ( ! preg_match('#^\d+\.\d+$#', $version) ){
            throw new Exception('Invalid format of the CHANGELOG file');
        }
        return $version;
    }
    
    public function getNextVersion($version, $major){
        $version = $this->getCurrentVersion();
        list($maj, $min) = explode('.', $version);
        return $major ? implode('.', array(++$maj,0)) : implode('.', array($maj,++$min));
    }

    public function update($version, $comment, $major){
        $changelog = file($this->filePath, FILE_IGNORE_NEW_LINES);
        $date = date('d/m/Y H:i');
        if ($major){
            list($maj, $min) = explode('.', $version);
            array_splice($changelog, 0, 0, array("", "Version $maj - $comment"));
            $comment = "initial release";
        }
        array_splice($changelog, 2, 0, array("    $date  $version  $comment"));
        file_put_contents($this->filePath, implode("\n", $changelog));
    }
}
