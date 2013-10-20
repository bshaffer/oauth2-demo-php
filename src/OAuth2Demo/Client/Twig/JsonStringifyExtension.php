<?php

namespace OAuth2Demo\Client\Twig;

class JsonStringifyExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'json_stringify' => new \Twig_Filter_Method($this, 'jsonStringify'),
        );
    }

    public function jsonStringify($json)
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            if (is_string($json)) {
                $json = json_decode($json, true);
            }
            return json_encode($json, JSON_PRETTY_PRINT);
        }
        return $this->jsonPrettyPrint($json);
    }

    public function getName()
    {
        return 'json_stringify';
    }

    private function jsonPrettyPrint($json)
    {
        if (is_array($json)) {
            $json = json_encode($json);
        }
        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = "\t";
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {

            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;

            // If this character is the end of an element,
            // output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }

                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }
}
