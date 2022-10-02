<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\UI;

use Movisio\RestfulApi\Application\Converters\ResourceConverter;
use Movisio\RestfulApi\Application\Responses\ErrorResponse;
use Movisio\RestfulApi\Http\IInput;
use Movisio\RestfulApi\Http\InputFactory;
use Movisio\RestfulApi\Security\AuthenticationContext;
use Movisio\RestfulApi\Security\ForbiddenRequestException;
use Movisio\RestfulApi\Security\SecurityException;
use Movisio\RestfulApi\Utils\RequestFilter;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\NotImplementedException;

/**
 * Basic resource presenter
 */
class ResourcePresenter extends Presenter
{
    /** @var array */
    protected array $resource = [];

    /** @var InputFactory */
    protected InputFactory $inputFactory;

    /** @var AuthenticationContext */
    protected AuthenticationContext $authentication;

    /** @var IInput */
    protected IInput $input;

    /** @var RequestFilter */
    protected RequestFilter $requestFilter;

    /** @var ResourceConverter */
    protected ResourceConverter $resourceConverter;

    /** @var array Default response code for each request method */
    protected $defaultCodes = [
        IRequest::GET => IResponse::S200_OK,
        IRequest::POST => IResponse::S201_CREATED,
        IRequest::PUT => IResponse::S200_OK,
        IRequest::HEAD => IResponse::S200_OK,
        IRequest::DELETE => IResponse::S200_OK,
        IRequest::PATCH => IResponse::S200_OK,
    ];

    /**
     * Send resouce as Json response
     * @param string|null $contentType
     * @return void
     */
    protected function sendResource(string $contentType = null) : void
    {
        if ($contentType && $contentType != 'application/json') {
            throw new NotImplementedException("Only JSON API responses are currently allowed");
        }
        $response = new JsonResponse($this->resourceConverter->convertResource($this->resource));
        $code = $this->getHttpResponse()->getCode();
        if ($code === IResponse::S200_OK) {
            $code = $this->defaultCodes[$this->getRequest()->getMethod()] ?? 200;
        }
        $this->getHttpResponse()->setCode($code);
        $this->sendResponse($response);
    }

    /**
     * Inject Restful services
     * @param AuthenticationContext $authentication
     * @param InputFactory $inputFactory
     * @param RequestFilter $requestFilter
     * @param ResourceConverter $resourceConverter
     */
    final public function injectDrahakRestful(
        AuthenticationContext $authentication,
        InputFactory $inputFactory,
        RequestFilter $requestFilter,
        ResourceConverter $resourceConverter,
    ) : void {
        $this->authentication = $authentication;
        $this->inputFactory = $inputFactory;
        $this->resourceConverter = $resourceConverter;
        $this->requestFilter = $requestFilter;
    }

    /**
     * Presenter startup
     */
    protected function startup() : void
    {
        parent::startup();
        $this->autoCanonicalize = false;
        $this->getHttpResponse()->setHeader('Access-Control-Allow-Origin', '*');

        $validationMethod = 'validate' . ucfirst($this->action);
        try {
            $validationExists = $this->tryCall($validationMethod, []);
        } catch (\Throwable $exception) {
            throwDebug($exception);
            $this->sendResponse(new ErrorResponse(new JsonResponse([
                'code' => $exception->getCode() ?: 500,
                'status' => 'error',
                'message' => 'Server error during request validation setup'
            ])));
        }

        if ($validationExists && !$this->getInput()->isValid()) {
            $this->sendResponse(new ErrorResponse(new JsonResponse([
                'code' => 422,
                'status' => 'validation-error',
                'messages' => $this->getInput()->validate(),
            ])));
        }
    }

    /**
     * On before render
     */
    protected function beforeRender() : void
    {
        parent::beforeRender();
        $this->sendResource();
    }

    /**
     * Check security and other presenter requirements
     * @param mixed $element
     */
    public function checkRequirements($element) : void
    {
        try {
            parent::checkRequirements($element);
        } catch (ForbiddenRequestException $e) {
            $this->sendErrorResource($e);
        }

        // Try to authenticate client
        try {
            $this->authentication->authenticate($this->getInput());
        } catch (SecurityException $e) {
            $this->sendErrorResource($e);
        }
    }

    /**
     * Get input
     * @return IInput
     */
    public function getInput() : IInput
    {
        if (!isset($this->input)) {
            try {
                $this->input = $this->inputFactory->create();
            } catch (BadRequestException $e) {
                $this->sendErrorResource($e);
            }
        }
        return $this->input;
    }
}
