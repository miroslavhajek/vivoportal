<?php
namespace Vivo\Indexer\Adapter;

use Vivo\Indexer\Query;
use Vivo\Indexer\Document;
use Vivo\Indexer\Result;
use Vivo\Indexer\QueryParams;
use Vivo\TransactionalInterface;

/**
 * AdapterInterface
 */
interface AdapterInterface extends TransactionalInterface
{
    /**
     * Finds documents matching the query in the index and returns a search result
     * If there are no documents found, returns an empty Result
     * @param \Vivo\Indexer\Query\QueryInterface $query
     * @param \Vivo\Indexer\QueryParams|null $queryParams
     * @return Result
     */
    public function find(Query\QueryInterface $query, QueryParams $queryParams = null);

    /**
     * Finds and returns a document by its ID
     * If the document is not found, returns null
     * @param string $docId
     * @return Document|null
     */
    public function findById($docId);

    /**
     * Adds a document into the index
     * @param \Vivo\Indexer\Document $doc
     * @return void
     */
    public function addDocument(Document $doc);

    /**
     * Updates document in index
     * @param Document $doc
     */
    public function update(Document $doc);

    /**
     * Deletes documents from the index
     * @param \Vivo\Indexer\Query\QueryInterface $query
     * @return void
     */
    public function delete(Query\QueryInterface $query);

    /**
     * Deletes document by its unique ID
     * @param string $docId
     */
    public function deleteById($docId);

    /**
     * Deletes all documents from index
     * @return void
     */
    public function deleteAllDocuments();

    /**
     * Optimizes the index
     * @return void
     */
    public function optimize();

    /**
     * Returns query as string
     * @param \Vivo\Indexer\Query\QueryInterface $query
     * @return string
     */
    public function getQueryString(Query\QueryInterface $query);
}
