<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Http;

use Movisio\RestfulApi\InvalidStateException;
use Movisio\RestfulApi\Mapping\IMapper;
use Movisio\RestfulApi\Mapping\MapperContext;
use Movisio\RestfulApi\Mapping\MappingException;
use Movisio\RestfulApi\Validation\IValidationScopeFactory;
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

    /** @var IValidationScopeFactory */
    private $validationScopeFactory;

    /** @var IMapper */
    private $mapper;

    /** @var MapperContext */
    private $mapperContext;

    /**
     * @param IRequest $httpRequest
     * @param MapperContext $mapperContext
     * @param IValidationScopeFactory $validationScopeFactory
     */
    public function __construct(
        IRequest $httpRequest,
        MapperContext $mapperContext,
        IValidationScopeFactory $validationScopeFactory
    ) {
        $this->httpRequest = $httpRequest;
        $this->mapperContext = $mapperContext;
        $this->validationScopeFactory = $validationScopeFactory;
    }

    /**
     * Create input
     * @return Input
     */
    public function create() : Input
    {
        $input = new Input($this->validationScopeFactory);
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
                $this->mapper = $this->mapperContext->getMapper($this->httpRequest->getHeader('Content-Type'));
                $requestBody = $this->mapper->parse($input);
            } catch (InvalidStateException $e) {
                throw BadRequestException::unsupportedMediaType(
                    'No mapper defined for Content-Type ' . $this->httpRequest->getHeader('Content-Type'),
                    $e
                );
            } catch (MappingException $e) {
                throw new BadRequestException($e->getMessage(), 400, $e);
            }
        }
        return $requestBody;
    }
}
