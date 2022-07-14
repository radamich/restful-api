<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Http;

/**
 * REST client request Input interface
 */
interface IInput extends \Drahak\Restful\Http\IInput // contravariance reasons
{
    /**
     * Get parsed input data
     * @return array
     */
    public function getData() : array;
}
