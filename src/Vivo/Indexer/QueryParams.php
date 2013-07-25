<?php
namespace Vivo\Indexer;

/**
 * QueryParams
 */
class  QueryParams
{
    /**
     * Result page size
     * @var int
     */
    protected $pageSize     = 10;

    /**
     * Result start offset
     * @var int
     */
    protected $startOffset  = 0;

    /**
     * List of sorting criteria
     * Array of field names which are optionally ended with 'asc'|'desc' (default 'asc')
     * @var string[]
     */
    protected $sort         = array();

    /**
     * Constructor
     * @param array $params
     * Supported $params keys:
     *  'page_size'
     *  'start_offset'
     *  'sort'
     */
    public function __construct(array $params = array())
    {
        if (array_key_exists('page_size', $params)) {
            $this->setPageSize($params['page_size']);
        }
        if (array_key_exists('start_offset', $params)) {
            $this->setStartOffset($params['start_offset']);
        }
        if (array_key_exists('sort', $params)) {
            $this->setSort($params['sort']);
        }
    }

    /**
     * Returns (max) number of rows the query should return
     * Enables paginating within the results
     * @return integer
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Returns row number (starting from 0) where the result set should begin
     * Enables paginating within the results
     * @return integer
     */
    public function getStartOffset()
    {
        return $this->startOffset;
    }

    /**
     * Sets the result page size
     * @param int $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * Sets the result start offset
     * @param int $startOffset
     */
    public function setStartOffset($startOffset)
    {
        $this->startOffset = $startOffset;
    }

    /**
     * Sets sorting
     * @param string[]|string $sort
     * @throws Exception\InvalidArgumentException
     */
    public function setSort($sort)
    {
        if (is_string($sort)) {
            $sort   = array($sort);
        }
        if (!is_array($sort)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Unsupported sort parameter type '%s'; use either string or an array",
                        __METHOD__, gettype($sort)));
        }
        $this->sort = $sort;
    }

    /**
     * Returns sorting
     * @return array|\string[]
     */
    public function getSort()
    {
        return $this->sort;
    }
}