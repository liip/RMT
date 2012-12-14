<?php

namespace Liip\RMT\Version\Persister;

class TagValidator
{

    public function __construct($regex, $tagPrefix='')
    {
        $this->regex = $regex;
        $this->tagPrefix = $tagPrefix;
    }

    /**
     * Check if a tag is valid
     * @param $tag
     * @return boolean
     */
    public function isValid($tag){
        if (strlen($this->tagPrefix) > 0 && strpos($tag,$this->tagPrefix) !== 0){
            return false;
        }
        return preg_match('/^'.$this->regex.'$/', substr($tag,strlen($this->tagPrefix))) == 1;
    }

    /**
     * Remove all invalid tags from a list
     */
    public function filtrateList($tags)
    {
        $validTags = array();
        foreach ($tags as $tag){
            if ($this->isValid($tag)){
                $validTags[] = $tag;
            }
        }
        return $validTags;
    }
}
