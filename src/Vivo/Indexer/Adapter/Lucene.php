<?php
namespace Vivo\Indexer\Adapter;

use Vivo\Indexer\Query;
use Vivo\Indexer\Exception;
use Vivo\Indexer\QueryHit;
use Vivo\Indexer\Document;
use Vivo\Indexer\Field;
use Vivo\Indexer\Term as IndexTerm;
use ZendSearch\Lucene as SearchLucene;

/**
 * Lucene
 * Lucene indexer adapter
 * The transaction processing is not truly transactional - the changes are not visible in the running transaction
 */
class Lucene implements AdapterInterface
{
    /**
     * Lucene index
     * @var SearchLucene\SearchIndexInterface
     */
    protected $index;

    /**
     * Is a transaction open?
     * @var bool
     */
    protected $transaction      = false;

    /**
     * Array of document IDs to be deleted within the transaction
     * @var string[]
     */
    protected $deleteIds        = array();

    /**
     * Array of documents to be added within the transaction
     * @var Document[]
     */
    protected $addDocs          = array();

    /**
     * Should any uncommitted changes be committed upon the adapter destruction?
     * @var bool
     */
    protected $commitOnDestruct = true;



//if ($isBinary) {
//$this->isTokenized  = false;
//$this->isStored     = true;
//$this->isIndexed    = false;
//} else {
//    $this->isTokenized  = $isTokenized;
//    $this->isStored     = $isStored;
//    $this->isIndexed    = $isIndexed;
//}
//$this->isBinary  = $isBinary;

//    /**
//     * Is the field binary?
//     * @return boolean
//     */
//    public function isBinary()
//    {
//        return $this->isBinary;
//    }
//
//    /**
//     * Is the field indexed?
//     * @return boolean
//     */
//    public function isIndexed()
//    {
//        return $this->isIndexed;
//    }
//
//    /**
//     * Is the field stored?
//     * @return boolean
//     */
//    public function isStored()
//    {
//        return $this->isStored;
//    }
//
//    /**
//     * Is the field tokenized?
//     * @return boolean
//     */
//    public function isTokenized()
//    {
//        return $this->isTokenized;
//    }












    /**
     * Constructor
     * @param \ZendSearch\Lucene\SearchIndexInterface $index
     */
    public function __construct(SearchLucene\SearchIndexInterface $index)
    {
        $this->index    = $index;
    }

    /**
     * Destructor
     * Optionally commits any uncommitted changes
     */
    public function __destruct()
    {
        if ($this->commitOnDestruct && $this->transaction) {
            $this->commit();
        }
    }

    /**
     * Finds documents matching the query in the index and returns an array of query hits
     * If there are no documents found, returns an empty array
     * @param \Vivo\Indexer\Query\QueryInterface $query
     * @throws \Vivo\Indexer\Exception\InvalidArgumentException
     * @return QueryHit[]
     */
    public function find(Query\QueryInterface $query)
    {
        $luceneQuery    = $this->buildLuceneQuery($query);
        $luceneHits     = $this->index->find($luceneQuery);
        $hits           = array();
        foreach ($luceneHits as $luceneHit) {
            /* @var $luceneHit SearchLucene\Search\QueryHit */
            if (!$this->isDeleted($luceneHit->document_id)) {
                //Include only undeleted documents in the result
                $hit        = new QueryHit(
                                $this,
                                (string)$luceneHit->id,
                                (string)$luceneHit->document_id,
                                $luceneHit->score);
                $hits[]     = $hit;
            }
        }
        return $hits;
    }

    /**
     * Returns if a transaction is currently open
     * @return bool
     */
    public function isTransactionOpen()
    {
        return $this->transaction;
    }

    /**
     * Commits changes and opens a new transaction
     */
    public function begin()
    {
        if ($this->isTransactionOpen()) {
            $this->commit();
        }
        $this->transaction = true;
    }

    /**
     * Commits changes
     */
    public function commit()
    {
        //Delete documents
        foreach ($this->deleteIds as $deleteId) {
            try {
                $this->index->delete($deleteId);
            } catch (SearchLucene\Exception\OutOfRangeException $e) {
                //Document id not found - silently suppress
            }
        }
        //Add documents
        foreach ($this->addDocs as $addDoc) {
            $luceneDoc = $this->createLuceneDocFromDoc($addDoc);
            $this->index->addDocument($luceneDoc);
        }
        //Commit delete changes
        $this->index->commit();
        //Reset the transaction
        $this->resetTransaction();
    }

    /**
     * Rolls back any scheduled changes and closes the transaction
     */
    public function rollback()
    {
        $this->resetTransaction();
    }

    /**
     * Resets transaction - discards any scheduled changes and closes the transaction
     */
    protected function resetTransaction()
    {
        $this->deleteIds    = array();
        $this->addDocs      = array();
        $this->transaction  = false;
    }

    /**
     * Finds documents based on a term
     * This is usually faster than find()
     * Returns an array of document ids, if no documents are found, returns an empty array
     * @param IndexTerm $term
     * @return array
     */
    public function termDocs(IndexTerm $term)
    {
        $luceneTerm     = new SearchLucene\Index\Term($term->getText(), $term->getField());
        $docIds         = $this->index->termDocs($luceneTerm);
        //Remove deleted documents from the result
        foreach ($docIds as $key => $docId) {
            if ($this->isDeleted($docId)) {
                unset($docIds[$key]);
            }
        }
        return $docIds;
    }

    /**
     * Returns a document by its ID
     * If the document with this ID does not exist, returns null
     * @param string $docId
     * @return Document|null
     */
    public function getDocument($docId)
    {
        if ($this->isDeleted($docId)) {
            //The document with this id is deleted
            $doc        = null;
        } else {
            try {
                $luceneDoc  = $this->index->getDocument($docId);
                $doc        = $this->createDocFromLuceneDoc($luceneDoc);
            } catch (SearchLucene\Exception\OutOfRangeException $e) {
                //$docId not found in index
                $doc        = null;
            }
        }
        return $doc;
    }

    /**
     * Deletes a document from the index
     * @param string $docId
     * @return void
     */
    public function deleteDocument($docId)
    {
        $this->deleteIds[] = $docId;
        if (!$this->isTransactionOpen()) {
            $this->commit();
        }
    }

    /**
     * Adds a document into the index
     * @param \Vivo\Indexer\Document $doc
     * @return void
     */
    public function addDocument(Document $doc)
    {
        $this->addDocs[] = $doc;
        if (!$this->isTransactionOpen()) {
            $this->commit();
        }
    }

    /**
     * Optimizes the index
     * If a transaction is open, first commits it
     * @return void
     */
    public function optimize()
    {
        if ($this->isTransactionOpen()) {
            $this->commit();
        }
        $this->index->optimize();
    }

    /**
     * Returns number of all (undeleted + deleted) documents in the index
     * @return integer
     */
    public function getDocumentCountAll()
    {
        return $this->index->count();
    }

    /**
     * Returns number of undeleted documents currently present in the index
     * @return integer
     */
    public function getDocumentCountUndeleted()
    {
        return $this->index->numDocs();
    }

    /**
     * Deletes all documents from index
     * @return void
     */
    public function deleteAllDocuments()
    {
        $this->commit();
        $maxDoc = $this->index->maxDoc() - 1;
        for ($i = 0; $i <= $maxDoc; $i++) {
            try {
                $this->index->delete($i);
            } catch (SearchLucene\Exception\OutOfRangeException $e) {
                //Silently suppress when doc id is not found
            }
        }
        $this->index->commit();
    }

    /**
     * Creates a document from a Lucene document
     * @param \ZendSearch\Lucene\Document $luceneDoc
     * @return \Vivo\Indexer\Document
     */
    protected function createDocFromLuceneDoc(SearchLucene\Document $luceneDoc)
    {
        $fieldNames = $luceneDoc->getFieldNames();
        $doc        = new Document();
        foreach ($fieldNames as $fieldName) {
            $luceneField    = $luceneDoc->getField($fieldName);
            $field          = new Field($luceneField->name, $luceneField->value,
                $luceneField->isStored, $luceneField->isIndexed,
                $luceneField->isTokenized, $luceneField->isBinary);
            $doc->addField($field);
        }
        return $doc;
    }

    /**
     * Creates a Lucene document from a document
     * @param \Vivo\Indexer\Document $doc
     * @return \ZendSearch\Lucene\Document
     */
    protected function createLuceneDocFromDoc(Document $doc)
    {
        $fieldNames = $doc->getFieldNames();
        $luceneDoc  = new SearchLucene\Document();
        foreach ($fieldNames as $fieldName) {
            $field          = $doc->getField($fieldName);
            $luceneField    = new \ZendSearch\Lucene\Document\Field($field->getName(), $field->getValue(), 'UTF-8',
                $field->isStored(), $field->isIndexed(), $field->isTokenized(), $field->isBinary());
            $luceneDoc->addField($luceneField);
        }
        return $luceneDoc;
    }

    /**
     * Builds and returns a Lucene query from Vivo query
     * @param Query\QueryInterface $query
     * @return SearchLucene\Search\Query\AbstractQuery
     * @throws Exception\InvalidArgumentException
     */
    protected function buildLuceneQuery(Query\QueryInterface $query)
    {
        if ($query instanceof Query\TermInterface) {
            //Term query
            /* @var $query Query\TermInterface */
            $term           = $query->getTerm();
            $luceneTerm     = new SearchLucene\Index\Term($term->getText(), $term->getField());
            $luceneQuery    = new SearchLucene\Search\Query\Term($luceneTerm);
        } elseif ($query instanceof Query\MultiTermInterface) {
            //Multi-term query
            /* @var $query Query\MultiTermInterface */
            $terms          = $query->getTerms();
            $signs          = $query->getSigns();
            $luceneQuery    = new SearchLucene\Search\Query\MultiTerm();
            foreach ($terms as $id => $term) {
                $luceneTerm = new SearchLucene\Index\Term($term->getText(), $term->getField());
                $luceneQuery->addTerm($luceneTerm, $signs[$id]);
            }
        } elseif ($query instanceof Query\WildcardInterface) {
            //Wildcard query
            /* @var $query Query\WildcardInterface */
            $pattern        = $query->getPattern();
            $luceneTerm     = new SearchLucene\Index\Term($pattern->getText(), $pattern->getField());
            $luceneQuery    = new SearchLucene\Search\Query\Wildcard($luceneTerm);
        } elseif ($query instanceof Query\BooleanInterface) {
            //Boolean query
            /* @var $query Query\BooleanInterface */

            $subqueries     = $query->getSubqueries();
            $signs          = $query->getSigns();
            $luceneQuery    = new SearchLucene\Search\Query\Boolean();
            foreach ($subqueries as $id => $subquery) {
                $luceneSubquery = $this->buildLuceneQuery($subquery);
                $luceneQuery->addSubquery($luceneSubquery, $signs[$id]);
            }
        } else {
            //Unsupported type of query
            throw new Exception\InvalidArgumentException(sprintf("%s: Unsupported query type '%s'",
                __METHOD__, get_class($query)));
        }
        return $luceneQuery;
    }

    /**
     * Returns if a document with the specified id is deleted
     * For $docId which is not present in index returns false
     * @param integer $docId
     * @return bool
     */
    protected function isDeleted($docId)
    {
        try {
            $isDel  = $this->index->isDeleted($docId);
        } catch (SearchLucene\Exception\OutOfRangeException $e) {
            //Document not found in index
            $isDel  = false;
        }
        return $isDel;
    }

    public function findById(string $docId)
    {
        // TODO: Auto-generated method stub
    }

    public function update(Document $doc)
    {
        // TODO: Auto-generated method stub
    }

    public function delete(QueryInterface $query)
    {
        // TODO: Auto-generated method stub
    }


    public function deleteById(string $docId)
    {
        // TODO: Auto-generated method stub
    }

    /**
     * Returns query as string
     * @param \Vivo\Indexer\Query\QueryInterface $query
     * @return string
     */
    public function getQueryString(QueryInterface $query)
    {
        // TODO: Auto-generated method stub
    }

}
