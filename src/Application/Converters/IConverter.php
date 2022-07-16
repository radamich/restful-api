<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\Converters;

/**
 * Common converter interface
 */
interface IConverter
{
    /**
     * Apply converter on resource data (keys or values usually, changing the structure should be possible too)
     * @param array $resource
     * @return array
     */
    public function convertResource(array $resource) : array;
}
