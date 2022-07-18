<?php
declare(strict_types = 1);

namespace Movisio\RestfulApi\Utils;

use Nette\Utils\Paginator;
use Drahak\Restful\InvalidStateException;
use Nette\Http\IRequest;
use Nette\Utils\Strings;

/**
 * RequestFilter
 *
 * @property-read array $fieldList
 * @property-read array $sortList
 * @property-read string $searchQuery
 * @property-read Paginator $paginator
 */
class RequestFilter
{
    /** Fields key in URL query */
    protected const FIELDS_KEY = 'fields';
    /** Sort key in URL query */
    protected const SORT_KEY = 'sort';
    /** Search string key in URL query */
    protected const SEARCH_KEY = 'q';

    /** Descending sort */
    protected const SORT_DESC = 'DESC';
    /** Ascending sort */
    protected const SORT_ASC = 'ASC';

    /** @var array */
    private array $fieldList;

    /** @var array */
    private array $sortList;

    /** @var Paginator */
    private Paginator $paginator;

    /** @var IRequest */
    private IRequest $request;

    /**
     * @param IRequest $request
     */
    public function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Get fields list
     * @return array
     */
    public function getFieldList() : array
    {
        if (!isset($this->fieldList)) {
            $this->fieldList = $this->createFieldList();
        }
        return $this->fieldList;
    }

    /**
     * Create sort list
     * @return array
     */
    public function getSortList() : array
    {
        if (!isset($this->sortList)) {
            $this->sortList = $this->createSortList();
        }
        return $this->sortList;
    }

    /**
     * Get search query
     * @return string|null
     */
    public function getSearchQuery() : ?string
    {
        return $this->request->getQuery('q');
    }

    /**
     * Get paginator
     * @param string|null $offset default value
     * @param string|null $limit default value
     * @return Paginator
     */
    public function getPaginator(string $offset = null, string $limit = null) : Paginator
    {
        if (!isset($this->paginator)) {
            $this->paginator = $this->createPaginator($offset, $limit);
        }
        return $this->paginator;
    }


    /**
     * Create sort list
     * @return array
     */
    protected function createSortList() : array
    {
        $sortList = [];
        $fields = array_filter(explode(',', $this->request->getQuery(self::SORT_KEY) ?? ''));
        foreach ($fields as $field) {
            $isInverted = Strings::substring($field, 0, 1) === '-';
            $sort = $isInverted ? self::SORT_DESC : self::SORT_ASC;
            $field = $isInverted ? Strings::substring($field, 1) : $field;
            $sortList[$field] = $sort;
        }
        return $sortList;
    }

    /**
     * Create field list
     * @return array
     */
    protected function createFieldList() : array
    {
        $fields = $this->request->getQuery(self::FIELDS_KEY) ?? [];
        return is_string($fields) ? array_filter(explode(',', $fields)) : $fields;
    }

    /**
     * Create paginator
     * @param int|null $offset
     * @param int|null $limit
     * @return Paginator
     */
    protected function createPaginator(int $offset = null, int $limit = null) : Paginator
    {
        $offset = $this->request->getQuery('offset') ?? $offset;
        $limit = $this->request->getQuery('limit') ?? $limit;

        if ($offset === null || $limit === null) {
            throw new InvalidStateException(
                'To create paginator add offset and limit query parameter to request URL'
            );
        }

        if ($limit == 0) {
            throw new InvalidStateException(
                'Pagination limit cannot be zero'
            );
        }

        $paginator = new Paginator();
        $paginator->setItemsPerPage($limit);
        $paginator->setPage(floor($offset / $limit) + 1);
        return $paginator;
    }
}
