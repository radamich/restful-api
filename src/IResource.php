<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi;

/**
 * IResource determines REST service result set
 */
interface IResource extends \Drahak\Restful\IResource // reversed for contravariance reasons
{
}
