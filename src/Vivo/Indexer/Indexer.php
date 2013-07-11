<?php
namespace Vivo\Indexer;

use Vivo\Indexer\Document;

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
     * Construct
     * @param Adapter\AdapterInterface $adapter
     */
    public function __construct(Adapter\AdapterInterface $adapter)
    {
        $this->adapter  = $adapter;
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
        return $this->adapter->find($query, $queryParams);
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
     * Returs query as string
     * @param Query\QueryInterface $query
     * @return string
     */
    public function getQueryString(Query\QueryInterface $query)
    {
        return $this->adapter->getQueryString($query);
    }
}
