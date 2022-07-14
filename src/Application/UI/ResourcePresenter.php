<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\UI;

use Movisio\RestfulApi\Http\IInput;
use Movisio\RestfulApi\Http\InputFactory;
use Movisio\RestfulApi\Security\AuthenticationContext;
use Movisio\RestfulApi\Security\ForbiddenRequestException;
use Movisio\RestfulApi\Security\SecurityException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\NotImplementedException;

/**
 * Basic resource presenter
 */
class ResourcePresenter extends Presenter
{
    /** @var array */
    protected array $resource = [];

    /** @var InputFactory */
    protected $inputFactory;

    /** @var AuthenticationContext */
    protected $authentication;

    /** @var IInput */
    private $input;


    /**
     * Send resouce as Json response
     * @param string $contentType
     * @return void
     */
    protected function sendResource(string $contentType = null) : void
    {
        if ($contentType && $contentType != 'application/json') {
            throw new NotImplementedException("Only JSON API responses are currently allowed");
        }
        $response = new JsonResponse(self::arrayKeysRecursiveConvert($this->resource));
        $this->sendResponse($response);
    }

    /**
     * Convert array keys from snake_case to camelCase recursively
     * @param iterable $array
     * @return array
     */
    private static function arrayKeysRecursiveConvert(iterable $array) : array
    {
        $res = [];
        foreach ($array as $key => $value) {
            $newKey = is_string($key) ? preg_replace_callback('/_([a-z])/', static function ($matches) {
                return strtoupper($matches[1]);
            }, $key) : $key;
            $newVal = is_iterable($value)  ? self::arrayKeysRecursiveConvert($value) : $value;
            $res[$newKey] = $newVal;
        }
        return $res;
    }

    /**
     * Inject Drahak Restful
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @param IResponseFactory|mixed $responseFactory
     * @param IResourceFactory|mixed $resourceFactory
     * @param AuthenticationContext $authentication
     * @param IInputFactory|mixed $inputFactory
     * @param RequestFilter|mixed $requestFilter
     */
    final public function injectDrahakRestful(
        ///*IResponseFactory*/ $responseFactory,
        ///*IResourceFactory*/ $resourceFactory,
        AuthenticationContext $authentication,
        InputFactory $inputFactory,
        ///*RequestFilter*/ $requestFilter
    ) : void {
        //$this->responseFactory = $responseFactory;
        //$this->resourceFactory = $resourceFactory;
        $this->authentication = $authentication;
        //$this->requestFilter = $requestFilter;
        $this->inputFactory = $inputFactory;
    }

    /**
     * Presenter startup
     *
     * @throws BadRequestException
     */
    protected function startup() : void
    {
        parent::startup();
        $this->autoCanonicalize = false;
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
        if (!$this->input) {
            try {
                $this->input = $this->inputFactory->create();
            } catch (BadRequestException $e) {
                $this->sendErrorResource($e);
            }
        }
        return $this->input;
    }
}
