<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Http;

use Movisio\RestfulApi\InvalidStateException;
use Movisio\RestfulApi\Application\BadRequestException;
use Nette\Http\IRequest;
use Nette;

/**
 * InputFactory
 */
class InputFactory
{
    /** @var IRequest */
    protected $httpRequest;

    /**
     * @param IRequest $httpRequest
     */
    public function __construct(
        IRequest $httpRequest
    ) {
        $this->httpRequest = $httpRequest;
    }

    /**
     * Create input
     * @return Input
     */
    public function create() : Input
    {
        $input = new Input();
        $input->setData($this->parseData());
        return $input;
    }

    /**
     * Parse data for input
     * @return array
     *
     * @throws BadRequestException
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
     * @return array|\Traversable
     *
     * @throws BadRequestException
     */
    protected function parseRequestBody()
    {
        $requestBody = [];
        $input = class_exists('Nette\Framework') && Nette\Framework::VERSION_ID <= 20200 ? // Nette 2.2.0 and/or newer
            file_get_contents('php://input') :
            $this->httpRequest->getRawBody();

        if ($input) {
            try {
                $requestBody = static::parseJson($input);
            } catch (InvalidStateException $e) {
                throw BadRequestException::unsupportedMediaType(
                    'No mapper defined for Content-Type ' . $this->httpRequest->getHeader('Content-Type'),
                    $e
                );
            } catch (Nette\Utils\JsonException $e) {
                throw new BadRequestException($e->getMessage(), 400, $e);
            }
        }
        return $requestBody;
    }

    /**
     * Convert client request data to array or traversable
     * @param string $data
     * @return array
     */
    public function parseJson(string $data) : array
    {
            return Nette\Utils\Json::decode($data, Json::FORCE_ARRAY);
    }
}
