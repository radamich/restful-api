<?php
declare(strict_types = 1);

namespace Drahak\Restful\Security\Process;

use Drahak\Restful\Http\IInput;

/**
 * NullAuthentication for non-secured API requests
 */
class NullAuthentication extends AuthenticationProcess
{
    /**
     * Authenticate request data
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @param IInput $input
     * @return bool
     */
    protected function authRequestData(IInput $input) : bool
    {
        return true;
    }

    /**
     * Authenticate request time
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @param IInput $input
     * @return bool
     */
    protected function authRequestTimeout(IInput $input) : bool
    {
        return true;
    }
}
