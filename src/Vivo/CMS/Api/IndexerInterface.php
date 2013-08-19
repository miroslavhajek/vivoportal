<?php
namespace Vivo\CMS\Api;

use Vivo\CMS\Model;
use Vivo\Indexer\Query\QueryInterface;
use Vivo\Indexer\QueryParams;
use Vivo\CMS\Model\Site;

/**
 * IndexerInterface
 * Indexer API interface
 */
interface IndexerInterface
{
    /**
     * Returns entities specified by the indexer query
     * @param QueryInterface|string $spec Either QueryInterface or a string query
     * @param QueryParams|array|null $queryParams Either a QueryParams object or an array specifying the params
     * @return Model\Entity[]
     */
    public function getEntitiesByQuery($spec, $queryParams = null);

    /**
     * Reindex all entities (contents and children) saved under the given path
     * Returns number of reindexed items
     * @param \Vivo\CMS\Model\Site $site
     * @param string $path Path to entity within the site
     * @param bool $deep If true reindexes whole subtree
     * @param bool $suppressErrors
     * @return int
     */
    public function reindex(Site $site, $path = '/', $deep = false, $suppressErrors = false);

    /**
     * Purges the indexer database
     * Removes all items from index
     */
    public function purge();
}
