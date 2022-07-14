<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Http;

use Movisio\RestfulApi\Resource\Link;
use Movisio\RestfulApi\Utils\RequestFilter;
use Movisio\RestfulApi\InvalidStateException;
use Nette\Http\IResponse;
use Nette\Http\IRequest;
use Nette\Http\Response;
use Nette\Utils\Paginator;

/**
 * ResponseFactory
 */
class ResponseFactory
{
    /** @var IRequest */
    private $request;

    /** @var IResponse */
    private $response;

    /** @var RequestFilter */
    private $requestFilter;

    /** @var array Default response code for each request method */
    protected $defaultCodes = [
        IRequest::GET => 200,
        IRequest::POST => 201,
        IRequest::PUT => 200,
        IRequest::HEAD => 200,
        IRequest::DELETE => 200,
        'PATCH' => 200,
    ];

    /**
     * @param IRequest $request
     * @param RequestFilter $requestFilter
     */
    public function __construct(IRequest $request, RequestFilter $requestFilter)
    {
        $this->request = $request;
        $this->requestFilter = $requestFilter;
    }

    /**
     * Set original wrapper response since nette does not support custom response codes
     * @param IResponse $response
     * @return ResponseFactory
     */
    public function setResponse(IResponse $response) : ResponseFactory
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Create HTTP response
     * @param int|NULL $code
     * @return IResponse
     */
    public function createHttpResponse(?int $code = null) : IResponse
    {
        $response = $this->response ? $this->response : new Response();
        $response->setCode($this->getCode($code));

        try {
            $response->setHeader('Link', $this->getPaginatorLink());
            $response->setHeader('X-Total-Count', $this->getPaginatorTotalCount());
        } catch (InvalidStateException $e) {
            // Don't use paginator
        }
        return $response;
    }

    /**
     * Get default status code
     * @param int|null $code
     * @return null
     */
    protected function getCode(?int $code = null)
    {
        if ($code === null) {
            $code = $this->defaultCodes[$this->request->getMethod()] ??
                200;
        }
        return (int)$code;
    }

    /**
     * Get paginator next/last link header
     * @return string
     */
    protected function getPaginatorLink() : string
    {
        $paginator = $this->requestFilter->getPaginator();

        $link = $this->getNextPageUrl($paginator);
        if ($paginator->getItemCount()) {
            $link .= ', ' . $this->getLastPageUrl($paginator);
        }
        return $link;
    }

    /**
     * Get paginator items total count
     * @return int|NULL
     */
    protected function getPaginatorTotalCount() : ?int
    {
        $paginator = $this->requestFilter->getPaginator();
        return $paginator->getItemCount() ? $paginator->getItemCount() : null;
    }

    /**
     * Get next page URL
     * @param Paginator $paginator
     * @return Link
     */
    private function getNextPageUrl(Paginator $paginator) : Link
    {
        $url = clone $this->request->getUrl();
        $query = [];
        parse_str($url->getQuery(), $query);
        $paginator->setPage($paginator->getPage() + 1);
        $query['offset'] = $paginator->getOffset();
        $query['limit'] = $paginator->getItemsPerPage();
        return new Link($url->withQuery($query), Link::NEXT);
    }

    /**
     * Get last page URL
     * @param Paginator $paginator
     * @return Link
     */
    private function getLastPageUrl(Paginator $paginator) : Link
    {
        $url = clone $this->request->getUrl();
        $query = [];
        parse_str($url->getQuery(), $query);
        $query['offset'] = $paginator->getLastPage() * $paginator->getItemsPerPage() - $paginator->getItemsPerPage();
        $query['limit'] = $paginator->getItemsPerPage();
        return new Link($url->withQuery($query), Link::LAST);
    }
}
