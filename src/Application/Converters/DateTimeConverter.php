<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\Converters;

use Nette\SmartObject;
use Traversable;
use DateTime;

/**
 * DateTimeConverter
 */
class DateTimeConverter implements IConverter
{
    use SmartObject;

    /** DateTime format */
    private string $format = 'c';

    /**
     * @param string $format of date time
     */
    public function __construct(string $format = 'c')
    {
        $this->format = $format;
    }

    /**
     * Converts DateTime objects in resource to string
     * @param array $resource
     * @return array
     */
    public function convertResource(array $resource) : array
    {
        return $this->formatDateTime($resource);
    }

    /**
     * @param mixed $resource
     * @return array
     */
    private function formatDateTime(array $resource) : array
    {
        foreach ($resource as $key => $value) {
            if ($value instanceof Traversable) {
                $resource[$key] = $this->formatDateTime(\iterator_to_array($value));
            }
            if (is_array($value)) {
                $resource[$key] = $this->formatDateTime($value);
            }
            if (
                $value instanceof DateTime
                || interface_exists('DateTimeInterface') && $value instanceof \DateTimeInterface
            ) {
                $resource[$key] = $value->format($this->format);
            }
        }
        return $resource;
    }
}
