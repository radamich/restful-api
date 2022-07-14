<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\Responses;

use Nette\Application\Response;
use Nette\Http;

/**
 * Class ErrorResponse
 */
class ErrorResponse implements Response
{
    /** @var array|\stdClass|\Traversable */
    protected $data;

    private Response $response;

    private int $code;

    /**
     * @param Response $response Wrapped response with data
     * @param int $errorCode
     */
    public function __construct(Response $response, int $code = 500)
    {
        $this->response = $response;
        $this->code = $code;
    }

    /**
     * Get response data
     * @return array|\stdClass|\Traversable
     */
    public function getData()
    {
        return $this->response->getData();
    }

    /**
     * Get response content type
     * @return string
     */
    public function getContentType() : string
    {
        return $this->response->contentType;
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
