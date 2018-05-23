<?php
namespace CseEightselectBasic\Components;

class StringHelper
{
    /**
     * @param  string $string
     * @return string
     */
    public static function formatString($string)
    {
        if ($string === '') {
            return $string;
        }

        $string = trim(preg_replace('/\s+/', ' ', $string));
        $string = str_replace('\\"', '"', $string);
        $string = str_replace('"', '\"', $string);
        return '"' . $string . '"';
    }
}
