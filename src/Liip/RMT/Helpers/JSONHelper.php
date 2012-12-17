<?php

namespace Liip\RMT\Helpers;

class JSONHelper
{
    /**
     * Format a one line JSON string
     *  Picked from here: http://php.net/manual/en/function.json-encode.php#80339
     *
     * @param $json
     * @param string $tab
     * @return bool|string
     */
    public static function format($json, $tab = "   ")
    {
        $formatted = "";
        $indentLevel = 0;
        $inString = false;

        // Normalized
        $jsonObj = json_decode($json);
        if($jsonObj === null) {
            throw new \InvalidArgumentException("Invalid JSON string");
        }
        $json = json_encode($jsonObj);

        // Format
        $len = strlen($json);
        for($c = 0; $c < $len; $c++) {
            $char = $json[$c];
            switch($char) {
                case '{':
                case '[':
                    if(!$inString) {
                        $formatted .= $char . "\n" . str_repeat($tab, $indentLevel+1);
                        $indentLevel++;
                    }
                    else {
                        $formatted .= $char;
                    }
                    break;
                case '}':
                case ']':
                    if(!$inString) {
                        $indentLevel--;
                        $formatted .= "\n" . str_repeat($tab, $indentLevel) . $char;
                    }
                    else {
                        $formatted .= $char;
                    }
                    break;
                case ',':
                    if(!$inString) {
                        $formatted .= ",\n" . str_repeat($tab, $indentLevel);
                    }
                    else {
                        $formatted .= $char;
                    }
                    break;
                case ':':
                    if(!$inString) {
                        $formatted .= ": ";
                    }
                    else {
                        $formatted .= $char;
                    }
                    break;
                case '"':
                    if($c > 0 && $json[$c-1] != '\\') {
                        $inString = !$inString;
                    }
                default:
                    $formatted .= $char;
                    break;
            }
        }

        return $formatted;
    }

}
