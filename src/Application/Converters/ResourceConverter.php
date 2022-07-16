<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\Converters;

/**
 * Manager for resource converters
 */
class ResourceConverter
{
    private array $converters = [];

    /**
     * @param IConverter $converter
     */
    public function addConverter(IConverter $converter) : void
    {
        $this->converters[] = $converter;
    }

    /**
     * Apply all converters one by one, no priority is defined (order of the addConverter() calls might not be fixed)
     * @param array $resource
     * @return array
     */
    public function convertResource(array $resource) : array
    {
        foreach ($this->converters as $converter) {
            $resource = $converter->convertResource($resource);
        }
        return $resource;
    }
}
