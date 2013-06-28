<?php
namespace Vivo\Indexer;

use Vivo\Indexer\Document;
use Vivo\Indexer\Query;

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
     * @var Vivo\Indexer\QueryBuilder
     */
    protected $qb;
    
    /**
     * QueryBuilder
     * @var Vivo\Indexer\QueryBuilder
     */    
    protected $defaultSearchableFields;
    
    /**
     * Construct
     * @param Adapter\AdapterInterface $adapter
     */
    public function __construct(Adapter\AdapterInterface $adapter)
    {
        $this->adapter                 = $adapter;
        $this->qb                      = new QueryBuilder();
        $this->defaultSearchableFields = array (
            '\title'           => 'title',
            '\resourceContent' => 'resourceContent',
        );
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
        return $this->adapter->find($query, $queryParams);
    }
    
    protected function checkAndRebuildQuery($query, $branch = 'left', $parent = null)
    {
        if($query instanceof Query\BooleanInterface) {
            $this->checkAndRebuildQuery($query->getQueryLeft(), 'left', $query);
            $this->checkAndRebuildQuery($query->getQueryRight(), 'right', $query);
        } else {
            if($query instanceof Query\Term) {
                if(!$query->getTerm()->getField()) {
                    return $this->rebuildQuery($query->getTerm(), $branch, $parent);
                }
            } elseif($query instanceof Query\Range) {
                if(!$query->getField()) {
                    return $this->rebuildQuery($query, $branch, $parent);
                }
            } elseif($query instanceof Query\Wildcard) {                
                if(!$query->getPattern()->getField()) {                    
                    return $this->rebuildQuery($query->getPattern(), $branch, $parent);
                }
            }
        }
        return $query;
    }
    
    protected function rebuildQuery($query, $branch, $parent = null)
    {
        $currentQuery = null;
        foreach ($this->defaultSearchableFields as $field => $name) {
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
}
