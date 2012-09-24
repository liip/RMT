<?php

namespace Liip;

class ArrayHelper
{
    /**
     * Return the requested key of an array
     * @param $array
     * @param $key
     * @param $default A default value in case the requested key is not present
     * @param $specificExceptionMsg A specific error msg
     * @return mixed The value from the array
     * @throws \InvalidArgumentException In case the key doesn't exist and not default are provided
     */
    public static function get($array, $key, $default = null, $specificExceptionMsg = null) {
        if (!(is_array($array) || $array instanceof \ArrayAccess)) {
            throw new \InvalidArgumentException("ArrayHelper::get() need an array as a first arg, get ". get_class($array));
        }
        elseif (array_key_exists($key, $array)) {
            return $array[$key];
        }
        elseif (isset($default)) {
            return $default;
        }
        else {
            $exceptionMsg = $specificExceptionMsg!==null ? $specificExceptionMsg : 'There is no key [%s] in the array';
            throw new \InvalidArgumentException(sprintf($exceptionMsg, $key));
        }
    }
}
