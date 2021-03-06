<?php
namespace Vivo\Indexer;

use Vivo\Indexer\Document;
use Vivo\Indexer\Query;

use Zend\EventManager\EventManager;
use Zend\Stdlib\ArrayUtils;

/**
 * Indexer
 */
class Indexer implements IndexerInterface
{
    /**
     * Indexer adapter
     * @var Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * QueryBuilder
     * @var \Vivo\Indexer\QueryBuilder
     */
    protected $qb;

    /**
     * Indexer options
     * @var array
     */
    protected $options = array('default_searchable_fields'=> array());

    /**
     * Event Manager
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Construct
     * @param Adapter\AdapterInterface $adapter
     * @param array $options
     */
    public function __construct(Adapter\AdapterInterface $adapter, array $options = array())
    {
        $this->adapter  = $adapter;
        $this->options  = ArrayUtils::merge($this->options, $options);
        $this->qb       = new QueryBuilder();
    }

    /**
     * Returns a search result
     * @param Query\QueryInterface $query
     * @param QueryParams|array|null $queryParams Either a QueryParams object or an array specifying the params
     * @see Vivo\Indexer\QueryParams for supported $queryParams keys
     * @return Result
     */
    public function find(Query\QueryInterface $query, $queryParams = null)
    {
        if (is_array($queryParams)) {
            $queryParams    = new QueryParams($queryParams);
        }

        $query = $this->checkAndRebuildQuery($query);
        $result = $this->adapter->find($query, $queryParams);
        return $result;
    }

    /**
     * Check for conditions with no field specified and rebuild query to search in common fields specified in conf
     * @param Query\QueryInterface $query
     * @param string $branch
     * @param Query\QueryInterface $parent
     * @return Query\QueryInterface $query
     */
    protected function checkAndRebuildQuery($query, $branch = 'left', $parent = null)
    {
        if($query instanceof Query\AbstractBooleanTwoOp) {
            $this->checkAndRebuildQuery($query->getQueryLeft(), 'left', $query);
            $this->checkAndRebuildQuery($query->getQueryRight(), 'right', $query);
        } elseif($query instanceof Query\BooleanNot) {
            $this->checkAndRebuildQuery($query->getQuery(), 'left', $query);
        } elseif($query instanceof Query\Term && !$query->getTerm()->getField()) {
            return $this->rebuildQuery($query->getTerm(), $branch, $parent);
        } elseif($query instanceof Query\Range && !$query->getField()) {
            return $this->rebuildQuery($query, $branch, $parent);
        } elseif($query instanceof Query\Wildcard && !$query->getPattern()->getField()) {
            return $this->rebuildQuery($query->getPattern(), $branch, $parent);
        }

        return $query;
    }

    /**
     * Rebuilds query to search in common fields specified in conf
     * @param Query\QueryInterface $query
     * @param string $branch
     * @param Query\QueryInterface $parent
     * @return Query\QueryInterface $query
     */
    protected function rebuildQuery($query, $branch, $parent = null)
    {
        $currentQuery = null;
        foreach ($this->options['default_searchable_fields'] as $field) {
            $newQuery = $this->qb->cond($query->getText(), $field);
            if($currentQuery != null){
                $currentQuery = $this->qb->orX($currentQuery, $newQuery);
            } else {
                $currentQuery = $newQuery;
            }
        }
        if($parent != null) {
            $setterName = 'setQuery' . $branch;
            $parent->$setterName($currentQuery);
            return $parent;
        } else {
            return $currentQuery;
        }
    }

    /**
     * Finds and returns a document by its ID
     * If the document is not found, returns null
     * @param string $docId
     * @return Document|null
     */
    public function findById($docId)
    {
        return $this->adapter->findById($docId);
    }

    /**
     * Deletes documents identified by a query from the index
     * @param Query\QueryInterface $query
     */
    public function delete(Query\QueryInterface $query)
    {
        $this->adapter->delete($query);
    }

    /**
     * Deletes document by its unique ID
     * @param string $docId
     */
    public function deleteById($docId)
    {
        $this->adapter->deleteById($docId);
    }

    /**
     * Adds a document into index
     * @param Document $document
     */
    public function addDocument(Document $document)
    {
        $this->adapter->addDocument($document);
    }

    /**
     * Optimizes the index
     * @return void
     */
    public function optimize()
    {
        $this->adapter->optimize();
    }

    /**
     * Commits pending changes and starts a new transaction
     */
    public function begin()
    {
        $this->adapter->begin();
    }

    /**
     * Commits pending changes and closes the transaction
     */
    public function commit()
    {
        $this->adapter->commit();
    }

    /**
     * Rolls back any scheduled changes and closes the transaction
     */
    public function rollback()
    {
        $this->adapter->rollback();
    }

    /**
     * Deletes all documents from index
     * @return void
     */
    public function deleteAllDocuments()
    {
        $this->adapter->deleteAllDocuments();
    }

    /**
     * Updates document in index
     * @param Document $document
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function update(Document $document)
    {
        if (!$document->getDocId()) {
            throw new Exception\InvalidArgumentException(
                sprintf('%s: Cannot update document; Document has no ID', __METHOD__));
        }
    }

    /**
     * Returns query as string
     * @param Query\QueryInterface $query
     * @return string
     */
    public function getQueryString(Query\QueryInterface $query)
    {
        return $this->adapter->getQueryString($query);
    }

    /**
     * Returns Event Manager
     * @return \Zend\EventManager\EventManager
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * Sets Event manager
     * @param \Zend\EventManager\EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
