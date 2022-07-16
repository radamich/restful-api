<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Http;

use Movisio\RestfulApi\BadRequestException;
use Nette\Http\IRequest;
use Nette;

/**
 * InputFactory
 */
class InputFactory
{
    /** @var IRequest */
    protected IRequest $httpRequest;

    /**
     * @param IRequest $httpRequest
     */
    public function __construct(IRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * Create input
     * @return Input
     */
    public function create() : Input
    {
        return new Input($this->parseData());
    }

    /**
     * Parse data for input
     * @return array
     */
    protected function parseData() : array
    {
        $postQuery = (array)$this->httpRequest->getPost();
        $urlQuery = (array)$this->httpRequest->getQuery();
        $requestBody = $this->parseRequestBody();

        return array_merge($urlQuery, $postQuery, $requestBody);    // $requestBody must be the last one!!!
    }

    /**
     * Parse request body if any
     * @return array
     */
    protected function parseRequestBody() : array
    {
        $requestBody = [];
        $input = class_exists('Nette\Framework') && Nette\Framework::VERSION_ID <= 20200 ? // Nette 2.2.0 and/or newer
            file_get_contents('php://input') :
            $this->httpRequest->getRawBody();

        if ($input) {
            try {
                $requestBody = static::parseJson($input);
            } catch (Nette\Utils\JsonException $e) {
                throw new BadRequestException($e->getMessage(), 400, $e);
            }
        }
        return $requestBody;
    }

    /**
     * Convert client request data to array
     * @param string $data
     * @return array
     */
    public function parseJson(string $data) : array
    {
            return Nette\Utils\Json::decode($data, Json::FORCE_ARRAY);
    }
}
