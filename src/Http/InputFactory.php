<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Http;

use Movisio\RestfulApi\BadRequestException;
use Movisio\RestfulApi\IResource;
use Nette\Http\IRequest;
use Nette;

/**
 * InputFactory
 */
class InputFactory
{
    use Nette\SmartObject;

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
        // Nette 2.2.0 and/or newer
        $input = class_exists(Nette\Framework::class) && Nette\Framework::VERSION_ID <= 20200 ?
            file_get_contents('php://input') :
            $this->httpRequest->getRawBody();
        $contentType = $this->httpRequest->getHeader('Content-Type');

        if ($input) {
            try {
                if ($contentType === IResource::JSON) {
                    $requestBody = static::parseJson($input);
                } elseif ($contentType === IResource::QUERY) {
                    $requestBody = static::parseQuery($input);
                } else {
                    throw new BadRequestException("'$contentType' content type not implemented");
                }
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
        return Nette\Utils\Json::decode($data, Nette\Utils\Json::FORCE_ARRAY);
    }

    /**
     * Convert client request data to array
     * @param string $data
     * @return array
     */
    public function parseQuery(string $data) : array
    {
        $result = [];
        parse_str($data, $result);
        return $result;
    }
}
