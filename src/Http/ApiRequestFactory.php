<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Http;

use Nette\Http\RequestFactory;
use Nette\Http\Request;
use Nette\Http\IRequest;

/**
 * Api request factory
 */
class ApiRequestFactory
{
    public const OVERRIDE_HEADER = 'X-HTTP-Method-Override';
    public const OVERRIDE_PARAM = '__method';

    /**
     * @var RequestFactory
     */
    private $factory;

    /**
     * @param RequestFactory $factory
     */
    public function __construct(RequestFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Create API HTTP request
     * @return IRequest
     */
    public function createHttpRequest() : IRequest
    {
        $request = $this->factory->createHttpRequest();
        $url = $request->getUrl();
        $url = $url->withQuery($request->getQuery());

        return new Request(
            $url,
            $request->getPost(),
            $request->getFiles(),
            $request->getCookies(),
            $request->getHeaders(),
            $this->getPreferredMethod($request),
            $request->getRemoteAddress(),
            null,
            static function () use ($request) {
                return $request->getRawBody();
            }
        );
    }

    /**
     * Get prederred method
     * @param  IRequest $request
     * @return string
     */
    protected function getPreferredMethod(IRequest $request) : string
    {
        $method = $request->getMethod();
        $isPost = $method === IRequest::POST;
        $header = $request->getHeader(self::OVERRIDE_HEADER);
        $param = $request->getQuery(self::OVERRIDE_PARAM);
        if ($header && $isPost) {
            return $header;
        }
        if ($param && $isPost) {
            return $param;
        }
        return $request->getMethod();
    }
}
