<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Application\UI;

use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\NotImplementedException;

/**
 * Basic resource presenter
 */
class ResourcePresenter extends Presenter
{
    /** @var array */
    protected array $resource;

    /**
     * Send resouce as Json response
     * @param string $contentType
     * @return void
     */
    protected function sendResource(string $contentType) : void
    {
        if ($contentType != 'application/json') {
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
            $newKey = preg_replace_callback('/_([a-z])/', static function ($matches) {
                return strtoupper($matches[1]);
            }, $key);
            $newVal = is_iterable($value)  ? self::arrayKeysRecursiveConvert($value) : $value;
            $res[$newKey] = $newVal;
        }
        return $res;
    }
}
