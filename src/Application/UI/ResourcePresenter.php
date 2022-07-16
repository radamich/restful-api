<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\UI;

use Movisio\RestfulApi\Application\Converters\ResourceConverter;
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
    protected InputFactory $inputFactory;

    /** @var AuthenticationContext */
    protected AuthenticationContext $authentication;

    /** @var IInput */
    private IInput $input;

    private ResourceConverter $resourceConverter;

    /**
     * @param ResourceConverter $resourceConverter
     */
    public function injectResourceConverter(ResourceConverter $resourceConverter) : void
    {
        $this->resourceConverter = $resourceConverter;
    }


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
        $this->sendResponse($response);
    }

    /**
     * Inject Drahak Restful
     * @param AuthenticationContext $authentication
     * @param InputFactory $inputFactory
     */
    final public function injectDrahakRestful(
        AuthenticationContext $authentication,
        InputFactory $inputFactory,
    ) : void {
        $this->authentication = $authentication;
        $this->inputFactory = $inputFactory;
    }

    /**
     * Presenter startup
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
