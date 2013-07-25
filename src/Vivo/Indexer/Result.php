<?php
namespace Vivo\Indexer;

/**
 * Result
 */
class Result
{
    /**
     * Query hits (hits actually contained in this result)
     * @var QueryHit[]
     */
    protected $hits;

    /**
     * Number of all hits the query would produce
     * @var integer
     */
    protected $totalHits;

    /**
     * Parameters the query was run with
     * @var QueryParams
     */
    protected $queryParams;

    /**
     * Constructor
     * @param QueryHit[] $hits
     * @param integer $totalHits
     * @param QueryParams|null $queryParams
     */
    public function __construct(array $hits, $totalHits, QueryParams $queryParams = null)
    {
        $this->hits         = $hits;
        $this->totalHits    = $totalHits;
        $this->queryParams  = $queryParams;
    }

    /**
     * Returns number of all hits the query would produce
     * @return int
     */
    public function getTotalHitCount()
    {
        return $this->totalHits;
    }

    /**
     * Returns number of hits actually contained in this result
     * @return int
     */
    public function getCount()
    {
        return count($this->hits);
    }

    /**
     * Returns parameters the query was run with
     * @return QueryParams
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Returns array of QueryHits
     * @return QueryHit[]
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * Returns first hit or NULL
     * @return QueryHit
     */
    public function getFirstHit()
    {
        return reset($this->getHits()) ?: null;
    }
}
