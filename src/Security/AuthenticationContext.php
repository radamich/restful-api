<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Security;

use Movisio\RestfulApi\Http\IInput;
use Movisio\RestfulApi\Security\Process\AuthenticationProcess;
use Nette\SmartObject;

/**
 * AuthenticationContext determines which authentication process should use
 */
class AuthenticationContext
{
    use SmartObject;

    /** @var AuthenticationProcess */
    private AuthenticationProcess $process;

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
     */
    public function authenticate(IInput $input) : bool
    {
        return $this->process->authenticate($input);
    }
}
