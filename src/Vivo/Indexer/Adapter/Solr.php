<?php
namespace Vivo\Indexer\Adapter;

use Vivo\Indexer\Result;
use Vivo\Indexer\QueryHit;
use Vivo\Indexer\Document;
use Vivo\Indexer\QueryParams;
use Vivo\Indexer\Query;
use Vivo\Indexer\Field;
use Vivo\CMS\Indexer\FieldHelperInterface;
use Vivo\Indexer\IndexerInterface;
use Vivo\CMS\Indexer\Exception\CannotDecomposeFieldnameException;
use Vivo\CMS\Indexer\Exception\InvalidArgumentException as IndexerInvalidArgumentException;

use VpApacheSolr\Document as SolrDocument;
use VpApacheSolr\Service as SolrService;

use Zend\EventManager\EventManager;

use \DateTime;

/**
 * Solr
 * Solr indexer adapter
 */
class Solr implements AdapterInterface
{
    /**
     * Solr service
     * @var SolrService
     */
    protected $solrService;

    /**
     * Is a transaction open?
     * @var bool
     */
    protected $transaction      = false;

    /**
     * Array of documents to be added within the transaction
     * @var Document[]
     */
    protected $addDocs          = array();

    /**
     * Array of document IDs to delete
     * @var string[]
     */
    protected $deleteIds        = array();

    /**
     * Array of queries specifying documents to delete
     * @var Query\QueryInterface[]
     */
    protected $deleteQueries    = array();

    /**
     * Flag - delete all documents during commit?
     * @var bool
     */
    protected $deleteAllDocs    = false;

    /**
     * Name of the unique id field in the index
     * @var string
     */
    protected $idField;

    /**
     * Name of the field containing the hit score
     * @var string
     */
    protected $scoreField       = 'score';

    /**
     * Direct mapping of vivo indexer field names to Solr field names
     * @var array
     */
    protected $fieldNameMap     = array(
        '\\uuid'     => 'uuid',
        '\\path'     => 'path',
        '\\class'    => 'class',
    );

    /**
     * Supported type suffices
     * @var array
     */
    protected $supportedTypeSuffices    = array(
        '_si',      //string, indexed
        '_sit',     //string, indexed, tokenized
        '_sim',     //string, indexed, multi-value
        '_ss',      //string, stored
        '_ssm',     //string, stored, multi-value
        '_sis',     //string, indexed, multi-value
        '_sist',    //string, indexed, stored, tokenized
        '_sism',    //string, indexed, stored, multi-value
        '_dti',     //date, indexed
        '_dtis',    //date, indexed, stored
        '_ii',      //integer, indexed
        '_iis',     //integer, indexed, stored
        '_iism',    //integer, indexed, stored, multi-value
        '_fi',      //float, indexed
        '_fis',     //float, indexed, stored
        '_fism',    //float, indexed, stored, multi-value
    );

    /**
     * Field Helper
     * @var FieldHelperInterface
     */
    protected $fieldHelper;

    /**
     * Constructor
     * @param SolrService $solrService
     * @param string $idField
     * @param FieldHelperInterface $fieldHelper
     * @param array $fieldNameMap
     * @param array $supportedTypeSuffices
     * @internal param array $fieldTypeMap
     */
    public function __construct(SolrService $solrService,
                                $idField,
                                FieldHelperInterface $fieldHelper,
                                array $fieldNameMap = array(),
                                array $supportedTypeSuffices = array())
    {
        $this->solrService              = $solrService;
        $this->idField                  = $idField;
        $this->fieldHelper              = $fieldHelper;
        $this->fieldNameMap             = array_merge($this->fieldNameMap, $fieldNameMap);
        $this->supportedTypeSuffices    = array_merge($this->supportedTypeSuffices, $supportedTypeSuffices);
    }
    
    /**
     * Finds documents matching the query in the index and returns a search result
     * If there are no documents found, returns an empty array
     * @param \Vivo\Indexer\Query\QueryInterface $query
     * @param \Vivo\Indexer\QueryParams|null $queryParams
     * @throws Exception\SolrServiceException
     * @return Result
     */
    public function find(Query\QueryInterface $query, QueryParams $queryParams = null)
    {
        if (is_null($queryParams)) {
            //Query params not specified => get all the documents, but first get their count
            $queryParams    = new QueryParams();
            $queryParams->setStartOffset(0);
            $queryParams->setPageSize(0);
            $countResult    = $this->find($query, $queryParams);
            $queryParams->setPageSize($countResult->getTotalHitCount());
        }
        $solrQuery  = $this->buildSolrQuery($query);
        $solrParams = array('fl' => '*,score');
        //Sorting
        $sortConds  = $queryParams->getSort();
        if (count($sortConds) > 0) {
            //Sorting is specified
            foreach ($sortConds as &$sortCond) {
                //Replace field name with Solr field name
                $words  =  explode(' ', $sortCond);
                $last   = strtolower(array_pop($words));
                if ($last == 'asc' || $last == 'desc') {
                    //Direction specified
                    $fieldName  = implode(' ', $words);
                } else {
                    //Direction not specified
                    $last       = 'asc';
                    $fieldName  = $sortCond;
                }
                $solrFieldName  = $this->getSolrFieldName($fieldName);
                $sortCond       = $solrFieldName . ' ' . $last;
            }
            $sort   = implode(', ', $sortConds);
            $solrParams['sort'] = $sort;
        }
        try {
            $events = new EventManager();
            //PerfLog
            $events->trigger('log', $this,
                array ('message'    => sprintf("Sending solr query '%s'", $solrQuery),
                    'priority'   => \VpLogger\Log\Logger::PERF_FINER));
            $solrResult = $this->solrService->search($solrQuery,
                                                     $queryParams->getStartOffset(),
                                                     $queryParams->getPageSize(),
                                                     $solrParams);
            //PerfLog
            $events->trigger('log', $this,
                array ('message'    => sprintf("Solr result returned for query '%s'", $solrQuery),
                    'priority'   => \VpLogger\Log\Logger::PERF_FINER));
        } catch (\Exception $e) {
            $ourException   = new Exception\SolrServiceException(
                sprintf("%s: Solr service threw an exception", __METHOD__),
                0,
                $e
            );
            throw $ourException;
        }
        $totalHits  = $solrResult->response->numFound;
        $resultSize = count($solrResult->response->docs);
        $hits           = array();
        for ($i = 0; $i < $resultSize; $i++) {
            /** @var $solrDoc SolrDocument */
            $solrDoc    = $solrResult->response->docs[$i];
            $doc        = $this->createDocFromSolrDoc($solrDoc);
            $hit        = new QueryHit($doc->getDocId(), $doc->getFieldValue($this->scoreField), $doc);
            $hits[]     = $hit;
        }
        $result     = new Result($hits, $totalHits, $queryParams);
        return $result;
    }


    /**
     * Finds and returns a document by its ID
     * If the document is not found, returns null
     * @param string $docId
     * @throws Exception\SolrServiceException
     * @return Document|null
     */
    public function findById($docId)
    {
        $solrQuery  = sprintf('%s:"%s"', $this->idField, $docId);
        try {
            $solrResult = $this->solrService->search($solrQuery, 0, 1);
        } catch (\Exception $e) {
            $ourException   = new Exception\SolrServiceException(
                sprintf("%s: Solr service threw an exception", __METHOD__),
                0,
                $e
            );
            throw $ourException;
        }
        if ($solrResult->response->numFound) {
            //Document found
            $solrDoc    = $solrResult->response->docs[0];
            $doc        = $this->createDocFromSolrDoc($solrDoc);
        } else {
            //Document not found
            $doc    = null;
        }
        return $doc;
    }

    /**
     * Deletes documents from the index
     * @param \Vivo\Indexer\Query\QueryInterface $query
     * @return void
     */
    public function delete(Query\QueryInterface $query)
    {
        $this->deleteQueries[]  = $query;
        if (!$this->isTransactionOpen()) {
            $this->commit();
        }
    }

    /**
     * Deletes document by its unique ID
     * @param string $docId
     */
    public function deleteById($docId)
    {
        if (!in_array($docId, $this->deleteIds)) {
            $this->deleteIds[]  = $docId;
        }
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
        $this->addDocs[]    = $doc;
        if (!$this->isTransactionOpen()) {
            $this->commit();
        }
    }

    /**
     * Optimizes the index
     * @throws Exception\SolrServiceException
     * @return void
     */
    public function optimize()
    {
        try {
            $this->solrService->optimize();
        } catch (\Exception $e) {
            $ourException   = new Exception\SolrServiceException(
                sprintf("%s: Solr service threw an exception", __METHOD__),
                0,
                $e
            );
            throw $ourException;
        }
    }

    /**
     * Deletes all documents from index
     * @return void
     */
    public function deleteAllDocuments()
    {
        $this->deleteAllDocs    = true;
        if (!$this->isTransactionOpen()) {
            $this->commit();
        }
    }

    public function begin()
    {
        if ($this->isTransactionOpen()) {
            $this->commit();
        }
        $this->transaction  = true;
    }

    public function commit()
    {
        //Delete documents
        if ($this->deleteAllDocs) {
            //Delete all docs
            try {
                $this->solrService->deleteByQuery('*:*');
            } catch (\Exception $e) {
                $ourException   = new Exception\SolrServiceException(
                    sprintf("%s: Solr service threw an exception", __METHOD__),
                    0,
                    $e
                );
                throw $ourException;
            }
        } else {
            //Delete by query
            foreach ($this->deleteQueries as $deleteQuery) {
                $solrQuery  = $this->buildSolrQuery($deleteQuery);
                try {
                    $this->solrService->deleteByQuery($solrQuery);
                } catch (\Exception $e) {
                    $ourException   = new Exception\SolrServiceException(
                        sprintf("%s: Solr service threw an exception", __METHOD__),
                        0,
                        $e
                    );
                    throw $ourException;
                }
            }
            //Delete specific docs
            foreach ($this->deleteIds as $deleteId) {
                try {
                    $this->solrService->deleteById($deleteId);
                } catch (\Exception $e) {
                    $ourException   = new Exception\SolrServiceException(
                        sprintf("%s: Solr service threw an exception", __METHOD__),
                        0,
                        $e
                    );
                    throw $ourException;
                }
            }
        }
        //Add documents
        foreach ($this->addDocs as $addDoc) {
            $solrDoc    = $this->createSolrDocFromDoc($addDoc);
            if ($addDoc->hasDocId()) {
                //Doc ID is set, add it to the Solr document
                $solrDoc->addField($this->idField, $addDoc->getDocId());
            }
            try {
                $extractableFields = $addDoc->getExtractableFields();
                if(count($extractableFields) == 1) {
                    $field = reset($extractableFields);
                    $extractParams = array(
                        'fmap.content' => $this->getSolrFieldName($field['name']),
                        'captureAttr'  => 'false',
                        'lowernames'   => 'true',
                        'commit' => 'true'
                    );                    
                    
                    $this->solrService->extractFromString($field['value'], $extractParams, $solrDoc);
                } elseif (count($extractableFields) > 1) {
                    $ourException   = new Exception\SolrServiceException(
                        sprintf("%s: Indexing more than one extracteble field is not implemented.", __METHOD__),
                        0,
                        $e
                    );
                    throw $ourException;
                } else {
                    $this->solrService->addDocument($solrDoc);
                }
            } catch (\Exception $e) {
                $ourException   = new Exception\SolrServiceException(
                    sprintf("%s: Solr service threw an exception", __METHOD__),
                    0,
                    $e
                );
                throw $ourException;
            }
        }
        //Commit changes
        try {
            $this->solrService->commit();
        } catch (\Exception $e) {
            $ourException   = new Exception\SolrServiceException(
                sprintf("%s: Solr service threw an exception", __METHOD__),
                0,
                $e
            );
            throw $ourException;
        }
        //Reset the transaction
        $this->resetTransaction();
    }

    public function rollback()
    {
        $this->resetTransaction();
    }

    /**
     * Builds and returns a Solr query from Vivo query
     * @param Query\QueryInterface $query
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    protected function buildSolrQuery(Query\QueryInterface $query)
    {
        if ($query instanceof Query\TermInterface) {
            //Term query
            /* @var $query Query\TermInterface */
            $term           = $query->getTerm();
            $escaped        = $this->solrService->escapePhrase($term->getText());
            if ($term->getField()) {
                $solrField      = $this->getSolrFieldName($term->getField());
                $solrQuery      = sprintf('%s:"%s"', $solrField, $escaped);
            } else {
                $solrQuery      = sprintf('"%s"', $escaped);
            }
        } elseif ($query instanceof Query\WildcardInterface) {
            //Wildcard query
            /* @var $query Query\WildcardInterface */
            $pattern        = $query->getPattern();
            $escaped        = $this->solrService->escapePhrase($pattern->getText());
            if ($pattern->getField()) {
                $solrField      = $this->getSolrFieldName($pattern->getField());
                $solrQuery      = sprintf('%s:%s', $solrField, $escaped);
            } else {
                $solrQuery      = sprintf('%s', $escaped);
            }
        } elseif ($query instanceof Query\BooleanAnd) {
            //Boolean AND query
            /* @var $query Query\BooleanAnd */
            $solrQueryLeft  = $this->buildSolrQuery($query->getQueryLeft());
            $solrQueryRight = $this->buildSolrQuery($query->getQueryRight());
            $solrQuery      = sprintf('(%s AND %s)', $solrQueryLeft, $solrQueryRight);
        } elseif ($query instanceof Query\BooleanOr) {
            //Boolean OR query
            /* @var $query Query\BooleanOr */
            $solrQueryLeft  = $this->buildSolrQuery($query->getQueryLeft());
            $solrQueryRight = $this->buildSolrQuery($query->getQueryRight());
            $solrQuery      = sprintf('(%s OR %s)', $solrQueryLeft, $solrQueryRight);
        } elseif ($query instanceof Query\BooleanNot) {
            //Boolean NOT query
            /* @var $query Query\BooleanNot */
            $positiveQuery  = $this->buildSolrQuery($query->getQuery());
            $solrQuery      = sprintf('NOT (%s)', $positiveQuery);
        } elseif ($query instanceof Query\RangeInterface) {
            //Range query
            /* @var $query Query\RangeInterface */
            $leftBracket    = $query->isLowerLimitInclusive() ? '[' : '{';
            $rightBracket   = $query->isUpperLimitInclusive() ? ']' : '}';
            $lowerLimit     = is_null($query->getLowerLimit()) ? '*' : $query->getLowerLimit();
            $upperLimit     = is_null($query->getUpperLimit()) ? '*' : $query->getUpperLimit();
            $solrField      = $this->getSolrFieldName($query->getField());
            $solrQuery      = sprintf('(%s:%s%s TO %s%s)', $solrField,
                                      $leftBracket, $lowerLimit, $upperLimit, $rightBracket);
        } else {
            //Unsupported type of query
            throw new Exception\InvalidArgumentException(sprintf("%s: Unsupported query type '%s'",
                __METHOD__, get_class($query)));
        }
        return $solrQuery;
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
     * Resets transaction - discards any scheduled changes and closes the transaction
     */
    protected function resetTransaction()
    {
        $this->deleteAllDocs    = false;
        $this->deleteQueries    = array();
        $this->deleteIds        = array();
        $this->addDocs          = array();
        $this->transaction      = false;
    }

    /**
     * Creates a Solr document from a Document
     * @param \Vivo\Indexer\Document $doc
     * @return SolrDocument
     */
    protected function createSolrDocFromDoc(Document $doc)
    {
        $solrDoc    = new SolrDocument();
        /** @var $field \Vivo\Indexer\Field */
        foreach ($doc as $field) {
            $solrFieldName  = $this->getSolrFieldName($field->getName());
            if ($field->isMultiValued()) {
                //MultiValued field
                foreach ($field->getValue() as $singleValue) {
                    $value  = $this->convertToSolrValue($singleValue, $field->getName());
                    if (!is_null($value) && !$field->getExtractable()) {
                        $solrDoc->addField($solrFieldName, $value);
                    }
                }
            } else {
                //SingleValued field
                $value  = $this->convertToSolrValue($field->getValue(), $field->getName());
                if (!is_null($value) && !$field->getExtractable()) {
                    $solrDoc->addField($solrFieldName, $value);
                }
            }
        }
        return $solrDoc;
    }

    /**
     * Creates Document from a Solr Document
     * @param SolrDocument $solrDoc
     * @return Document
     */
    protected function createDocFromSolrDoc(SolrDocument $solrDoc)
    {
        $doc        = new Document();
        foreach ($solrDoc as $fieldName => $fieldValue) {
            $vivoFieldName  = $this->getVivoFieldName($fieldName);
            $vivoValue      = $this->convertToVivoValue($fieldValue, $vivoFieldName);
            $field  = new Field($vivoFieldName, $vivoValue);
            $doc->addField($field);
        }
        $doc->setDocId($solrDoc->__get($this->idField));
        return $doc;
    }

    /**
     * Updates document in index
     * @param Document $doc
     */
    public function update(Document $doc)
    {
        $this->addDocument($doc);
    }

    /**
     * Creates Solr field name from a Vivo field name
     * @param string $vivoFieldName
     * @throws Exception\FieldTypeNotSupportedByIndexerAdapterException
     * @return string
     */
    protected function getSolrFieldName($vivoFieldName)
    {
        if (array_key_exists($vivoFieldName, $this->fieldNameMap)) {
            //Direct field name mapping found
            $solrFieldName  = $this->fieldNameMap[$vivoFieldName];
        } else {
            //Build the dynamic solr field name
            $idxConfig  = $this->fieldHelper->getIndexerConfigForFieldName($vivoFieldName);
            $solrSuffix = $this->getTypeSuffix($idxConfig);
            //Replace backslashes with underscores
            $solrFieldName  = str_replace('\\', '_', $vivoFieldName);
            $solrFieldName  .= $solrSuffix;
        }
        return $solrFieldName;
    }

    /**
     * Creates Vivo field name from a Solr field name
     * @param string $solrFieldName
     * @return string
     */
    protected function getVivoFieldName($solrFieldName)
    {
        $vivoFieldName  = array_search($solrFieldName, $this->fieldNameMap);
        if ($vivoFieldName === false) {
            //Field name not found in direct mappings
            foreach ($this->supportedTypeSuffices as $solrSuffix) {
                $solrSuffixLen  = strlen($solrSuffix);
                if (substr($solrFieldName, -1 * $solrSuffixLen) == $solrSuffix) {
                    $solrBareName   = substr($solrFieldName, 0, strlen($solrFieldName) - $solrSuffixLen);
                    $vivoFieldName  = str_replace('_', '\\', $solrBareName);
                    break;
                }
            }
            if ($vivoFieldName === false) {
                //No suffix matched, use Solr field name as Vivo field name
                $vivoFieldName  = $solrFieldName;
            }
        }
        return $vivoFieldName;
    }

    /**
     * Returns Solr type suffix based on the Vivo type and indexing options
     * @param array $indexerConfig
     * @throws Exception\FieldTypeNotSupportedByIndexerAdapterException
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    protected function getTypeSuffix(array $indexerConfig)
    {
        $requiredKeys   = array('type', 'indexed', 'stored', 'tokenized', 'multi');
        foreach($requiredKeys as $key) {
            if (!array_key_exists($key, $indexerConfig)) {
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: Indexer config key '%s' missing", __METHOD__, $key));
            }
        }
        $typeSuffix     = '_';
        $vivoType       = strtolower($indexerConfig['type']);
        switch ($vivoType) {
            case IndexerInterface::FIELD_TYPE_STRING:
                $typeSuffix .= 's';
                break;
            case IndexerInterface::FIELD_TYPE_DATETIME:
                $typeSuffix .= 'dt';
                break;
            case IndexerInterface::FIELD_TYPE_INT:
                $typeSuffix .= 'i';
                break;
            case IndexerInterface::FIELD_TYPE_FLOAT:
                $typeSuffix .= 'f';
                break;
            default:
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: Vivo field type '%s' not supported by Solr adapter.", __METHOD__, $vivoType));
                break;
        }
        //Indexed
        if ($indexerConfig['indexed']) {
            $typeSuffix   .= 'i';
        }
        //Stored
        if ($indexerConfig['stored']) {
            $typeSuffix   .= 's';
        }
        //Tokenized
        if ($indexerConfig['tokenized']) {
            $typeSuffix   .= 't';
        }
        //Multi-value
        if ($indexerConfig['multi']) {
            $typeSuffix   .= 'm';
        }
        //Check that the suffix is supported
        if (!in_array($typeSuffix, $this->supportedTypeSuffices)) {
            throw new Exception\FieldTypeNotSupportedByIndexerAdapterException(
                sprintf("%s: Solr type suffix '%s' not supported", __METHOD__, $typeSuffix));
        }
        return $typeSuffix;
    }

    /**
     * Converts a vivo value to solr value
     * Empty values are converted to null
     * @param mixed $vivoValue A string, DateTime, etc.
     * @param string $vivoFieldName
     * @throws Exception\InvalidArgumentException
     * @return string|null
     */
    protected function convertToSolrValue($vivoValue = null, $vivoFieldName)
    {
        $idxConfig  = $this->fieldHelper->getIndexerConfigForFieldName($vivoFieldName);
        $type       = $idxConfig['type'];
        switch ($type) {
            //String
            case IndexerInterface::FIELD_TYPE_STRING:
                if (is_null($vivoValue)) {
                    $solrValue = null;
                } else {
                    $solrValue = (string) $vivoValue;
                }
                break;
            //DateTime
            case IndexerInterface::FIELD_TYPE_DATETIME:
                if (is_null($vivoValue)) {
                    $solrValue = null;
                } else {
                    if (!$vivoValue instanceof DateTime) {
                        throw new Exception\InvalidArgumentException(
                                sprintf("%s: A DateTime value expected for 'datetime' indexer type", __METHOD__));
                    }
                    $solrValue = $vivoValue->format('Y-m-d\TH:i:s\Z');
                }
                break;
            case IndexerInterface::FIELD_TYPE_INT:
                if (is_null($vivoValue)) {
                    $solrValue = null;
                } else {
                    $solrValue = intval($vivoValue);
                }
                break;
            case IndexerInterface::FIELD_TYPE_FLOAT:
                if (is_null($vivoValue)) {
                    $solrValue = null;
                } else {
                    $solrValue = floatval($vivoValue);
                }
                break;
            default:
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: Indexer type '%s' not supported", __METHOD__, $type));
                break;
        }
        return $solrValue;
    }

    /**
     * Converts a solr value to a Vivo value
     * @param string $solrValue
     * @param string $vivoFieldName
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    protected function convertToVivoValue($solrValue, $vivoFieldName)
    {
        try {
            $idxConfig  = $this->fieldHelper->getIndexerConfigForFieldName($vivoFieldName);
        } catch (CannotDecomposeFieldnameException $e) {
            //Unknown field name (neither preset nor dynamic field name); E.g. 'timestamp' automatically added by Solr
            return $solrValue;
        } catch (IndexerInvalidArgumentException $e) {
            //Field name extracted, but indexer config not found
            return $solrValue;
        }
        $type       = $idxConfig['type'];
        switch ($type) {
            //String
            case IndexerInterface::FIELD_TYPE_STRING:
                $vivoValue  = $solrValue;
                break;
            //DateTime
            case IndexerInterface::FIELD_TYPE_DATETIME:
                $vivoValue  = new DateTime($solrValue);
                break;
            //Numeric
            case IndexerInterface::FIELD_TYPE_INT:
                $vivoValue  = intval($solrValue);
                break;
            case IndexerInterface::FIELD_TYPE_FLOAT:
                $vivoValue  = floatval($solrValue);
                break;
            default:
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: Indexer type '%s' not supported", __METHOD__, $type));
                break;
        }
        return $vivoValue;
    }
}
