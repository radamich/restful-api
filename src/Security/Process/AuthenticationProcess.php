<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Security\Process;

use Movisio\RestfulApi\Http\IInput;
use Nette\SmartObject;

/**
 * API authentication base class
 */
abstract class AuthenticationProcess
{
    use SmartObject;

    /**
     * Authenticate process
     * @param IInput $input
     * @return bool
     */
    public function authenticate(IInput $input) : bool
    {
        $this->authRequestData($input);
        $this->authRequestTimeout($input);
        return true;
    }

    /**
     * Authenticate request data
     * @param IInput $input
     */
    abstract protected function authRequestData(IInput $input) : void;

    /**
     * Authenticate request timeout
     * @param IInput $input
     */
    abstract protected function authRequestTimeout(IInput $input) : void;
}
