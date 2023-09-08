<?php

namespace DBCO\Shared\Application\Helpers;

/**
 * Case convertor helping to convert database fieldnames to variable names
 *
 * Class CaseConvertor
 * @package DBCO\Shared\Application\Helpers
 */
class TextCaseConvertor
{
    /**
     * Convert Camel cased string to Snake cased
     *
     * @param string $input
     * @return string
     */
    public static function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Convert Snake cased string to Camel cased
     *
     * @param string $input
     * @return string
     */
    public static function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }
}
