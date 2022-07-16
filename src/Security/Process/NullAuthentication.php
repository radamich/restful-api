<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Security\Process;

use Movisio\RestfulApi\Http\IInput;

/**
 * NullAuthentication for non-secured API requests
 */
class NullAuthentication extends AuthenticationProcess
{
    /**
     * Authenticate request data
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @param IInput $input
     */
    protected function authRequestData(IInput $input) : void
    {
    }

    /**
     * Authenticate request time
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @param IInput $input
     */
    protected function authRequestTimeout(IInput $input) : void
    {
    }
}
