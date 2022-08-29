<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\Converters;

use Nette\SmartObject;
use Nette\Utils\Strings;

/**
 * Convert snake_case keys to camelCase
 */
class CamelCaseConverter implements IConverter
{
    use SmartObject;

    /**
     * @param array $resource
     * @return array
     */
    public function convertResource(array $resource) : array
    {
        return self::arrayKeysRecursiveConvert($resource);
    }

    /**
     * Convert array keys from snake_case to camelCase recursively
     * @param iterable $array
     * @return array
     */
    private static function arrayKeysRecursiveConvert(iterable $array) : array
    {
        $res = [];
        foreach ($array as $key => $value) {
            $newKey = is_string($key) ? preg_replace_callback('/_([a-z])/', static function ($matches) {
                return strtoupper($matches[1]);
            }, Strings::firstLower($key)) : $key;
            $newVal = is_iterable($value)  ? self::arrayKeysRecursiveConvert($value) : $value;
            $res[$newKey] = $newVal;
        }
        return $res;
    }
}
