<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\Routes;

use Nette\Application\Routers\Route;
use Nette\Http\IRequest;

/**
 * Config-instantiable route class
 */
class ResourceRoute extends Route
{
    /** Resource methods */
    public const GET = 4;
    public const POST = 8;
    public const PUT = 16;
    public const DELETE = 32;
    public const HEAD = 64;
    public const PATCH = 128;
    public const OPTIONS = 256;

    /** @var int */
    protected int $flags;

    /** @var array */
    private array $methodDictionary = [
        IRequest::GET => self::GET,
        IRequest::POST => self::POST,
        IRequest::PUT => self::PUT,
        IRequest::HEAD => self::HEAD,
        IRequest::DELETE => self::DELETE,
        IRequest::PATCH => self::PATCH,
        IRequest::OPTIONS => self::OPTIONS,
    ];

    /**
     * Route constructor
     *
     * $flags argument has been deprecated in Nette, but we allow it to enable simpler router config files
     * The router factory should be aware of that and handle it - LazyRouter does for us.
     *
     * @param string $mask
     * @param array|string $metadata
     * @param int $flags
     */
    public function __construct(string $mask, $metadata = [], int $flags = self::GET)
    {
        if (is_string($metadata)) {
            $metadataParts = explode(':', $metadata);
            $metadata = [
                'module' => $metadataParts[0],
                'presenter' => $metadataParts[1],
                'action' => $metadataParts[2],
            ];
        }

        $this->flags = $flags;
        parent::__construct($mask, $metadata);
    }

    /**
     * override original flags which are deprecated
     */
    public function getFlags() : int
    {
        return $this->flags;
    }

    /**
     * Is this route mapped to given method
     * @param int $method
     * @return bool
     */
    public function isMethod(int $method) : bool
    {
        return ($this->getFlags() & $method) == $method;
    }

    /**
     * Get request method flag
     * @param IRequest $httpRequest
     * @return int|null
     */
    public function getMethod(IRequest $httpRequest) : ?int
    {
        $method = $httpRequest->getMethod();
        if (!isset($this->methodDictionary[$method])) {
            return null;
        }
        return $this->methodDictionary[$method];
    }

    /**
     * @param IRequest $httpRequest
     * @return array|null
     */
    public function match(IRequest $httpRequest) : ?array
    {
        $appRequest = parent::match($httpRequest);
        if (!$appRequest) {
            return null;
        }

        // Check requested method
        $methodFlag = $this->getMethod($httpRequest);
        if (!$this->isMethod($methodFlag)) {
            return null;
        }

        return $appRequest;
    }
}
