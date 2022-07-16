<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\Routes;

use Nette\Application\Routers\Route;

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
}
