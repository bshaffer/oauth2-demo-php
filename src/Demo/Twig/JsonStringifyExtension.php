<?php

namespace Demo\Twig;

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
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            return json_encode($json, JSON_PRETTY_PRINT);
        }
        $pattern = array(',"', '{', '}');
        $replacement = array(",\n\t\"", "{\n\t", "\n}");
        return str_replace($pattern, $replacement, json_encode($json));
    }

    public function getName()
    {
        return 'json_stringify';
    }
}
