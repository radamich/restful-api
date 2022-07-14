<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Security;

use Movisio\RestfulApi\Http\IInput;
use Movisio\RestfulApi\Security\Process\AuthenticationProcess;

/**
 * AuthenticationContext determines which authentication process should use
 * @property-write AuthenticationProcess $process
 */
class AuthenticationContext
{
    /** @var AuthenticationProcess */
    private $process;

    /**
     * Set authentication process to use
     * @param AuthenticationProcess $process
     * @return AuthenticationContext
     */
    public function setAuthProcess(AuthenticationProcess $process) : AuthenticationContext
    {
        $this->process = $process;
        return $this;
    }

    /**
     * Authenticate request with authentication process strategy
     * @param IInput $input
     * @return bool
     *
     * @throws AuthenticationException
     * @throws RequestTimeoutException
     */
    public function authenticate(IInput $input) : bool
    {
        return $this->process->authenticate($input);
    }
}
