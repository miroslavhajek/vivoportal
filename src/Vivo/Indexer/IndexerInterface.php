<?php
namespace Vivo\Indexer;

use Vivo\TransactionalInterface;

/**
 * IndexerInterface
 */
interface IndexerInterface extends TransactionalInterface
{
    const FIELD_TYPE_STRING     = 'string';
    const FIELD_TYPE_DATETIME   = 'datetime';
    const FIELD_TYPE_INT        = 'int';
    const FIELD_TYPE_FLOAT      = 'float';


    /**
     * Returns a search result
     * @param Query\QueryInterface $query
     * @param QueryParams|array|null $queryParams Either a QueryParams object or an array specifying the params
     * @see Vivo\Indexer\QueryParams for supported $queryParams keys
     * @return Result
     */
    public function find(Query\QueryInterface $query, $queryParams = null);

    /**
     * Finds and returns a document by its ID
     * If the document is not found, returns null
     * @param string $docId
     * @return Document|null
     */
    public function findById($docId);

    /**
     * Adds a document into index
     * @param Document $document
     */
    public function addDocument(Document $document);

    /**
     * Updates document in index
     * @param Document $document
     */
    public function update(Document $document);

    /**
     * Deletes documents identified by a query from the index
     * @param Query\QueryInterface $query
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
     * @param Query\QueryInterface $query
     * @return string
     */
    public function getQueryString(Query\QueryInterface $query);
}