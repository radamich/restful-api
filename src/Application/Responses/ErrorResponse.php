<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\Responses;

use Nette\Application\Response;
use Nette\Http;
use Nette\SmartObject;

/**
 * Class ErrorResponse
 */
class ErrorResponse implements Response
{
    use SmartObject;

    private Response $response;

    private int $code;

    /**
     * @param Response $response Wrapped response with data
     * @param int $code
     */
    public function __construct(Response $response, int $code = 500)
    {
        $this->response = $response;
        $this->code = $code;
    }

    /**
     * Get response data
     * @return int
     */
    public function getCode() : int
    {
        return $this->code;
    }

    /**
     * Sends response to output
     * @param Http\IRequest $httpRequest
     * @param Http\Response $httpResponse
     */
    public function send(Http\IRequest $httpRequest, Http\IResponse $httpResponse) : void
    {
        $httpResponse->setCode($this->code);
        $this->response->send($httpRequest, $httpResponse);
    }
}
