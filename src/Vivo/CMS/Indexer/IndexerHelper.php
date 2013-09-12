<?php
namespace Vivo\CMS\Indexer;

use Vivo\CMS\Model\Entity;
use Vivo\Indexer\Document;
use Vivo\Indexer\Field;
use Vivo\Indexer\Term as IndexerTerm;
use Vivo\Indexer\Query\Wildcard as WildcardQuery;
use Vivo\Indexer\Query\BooleanOr;
use Vivo\Indexer\Query\Term as TermQuery;
use Vivo\CMS\Model\Document as DocumentModel;
use Vivo\CMS\Api\Content\File as FileApi;
use Vivo\CMS\Model\Content\File;

use Zend\Stdlib\ArrayUtils;

/**
 * IndexerHelper
 * Generates indexer documents for entities, builds indexer queries based on entity properties
 */
class IndexerHelper implements IndexerHelperInterface
{
    /**
     * Indexer field helper
     * @var FieldHelperInterface
     */
    protected $indexerFieldHelper;

    /**
     * File Api
     * @var FileApi
     */
    protected $fileApi;

    /**
     * Indexer helper options
     * @var array
     */
    protected $options  = array(
        'extractable_mime_types'    => array(

        ),
    );

    /**
     * Constructor
     * @param FieldHelperInterface $indexerFieldHelper
     * @param FileApi $fileApi
     * @param array $options
     */
    public function __construct(FieldHelperInterface $indexerFieldHelper, FileApi $fileApi, array $options = array())
    {
        $this->indexerFieldHelper   = $indexerFieldHelper;
        $this->fileApi              = $fileApi;
        $this->options              = ArrayUtils::merge($this->options, $options);
    }

    /**
     * Creates an indexer document for the submitted entity
     * @param \Vivo\CMS\Model\Entity $entity
     * @param mixed|null $options Various options required to index the entity
     * @throws Exception\MethodNotFoundException
     * @return \Vivo\Indexer\Document
     */
    public function createDocument(Entity $entity, array $options = array())
    {
        $doc            = new Document();
        $entityClass    = get_class($entity);
        //Fields added by default
        //Published content types
        if ($entity instanceof DocumentModel) {
            if (array_key_exists('published_content_types', $options)
                && is_array($options['published_content_types'])) {
                //There are some published contents
                $doc->addField(new Field('\publishedContents', $options['published_content_types']));
            }
        } elseif($entity instanceof File) {
            try {
                $data = $this->fileApi->getResource($entity);
            } catch(\Exception $e) {
                //In the case of creation of the document,
                //the resource does not exist and is indexed after save to repository.
            }
            if(isset($data)) {
                $entityMimeType = $entity->getmimeType();
                if (strpos($entityMimeType, 'text/') === 0) {
                    //Textual data
                    if ($entityMimeType == 'text/html') {
                        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
                    }
                    $field  = new Field('\resourceContent', $data);
                    $doc->addField($field);
                } elseif ($this->isMimeTypeExtractable($entityMimeType)) {
                    //Extractable data
                    $field  = new Field('\resourceContent', $data, true);
                    $doc->addField($field);
                }
            }
        }
        //Class field
        $doc->addField(new Field('\class', $entityClass));

        //Fields added by metadata config
        $indexerConfigs  = $this->indexerFieldHelper->getIndexerConfig($entityClass);
        foreach ($indexerConfigs as $property => $indexerConfig) {
            $getter = 'get' . ucfirst($property);
            $value  = $entity->$getter();
            $doc->addField(new Field($indexerConfig['name'], $value));
        }
        return $doc;
    }

    /**
     * Returns if the specified mime type represents an extractable data type
     * @param string $mimeType
     * @return bool
     */
    protected function isMimeTypeExtractable($mimeType)
    {
        $isExtractable  = false;
        $mimeType       = strtolower($mimeType);
        foreach ($this->options['extractable_mime_types'] as $mimeTypeTpl) {
            if (is_null($mimeTypeTpl)) {
                continue;
            }
            $mimeTypeTpl    = strtolower($mimeTypeTpl);
            if ($mimeType == $mimeTypeTpl) {
                $isExtractable    = true;
                break;
            }
        }
        return $isExtractable;
    }

    /**
     * Builds and returns a query returning a whole subtree of documents beginning at the $entity
     * @param Entity|string $spec Either an Entity object or a path to an entity
     * @throws Exception\InvalidArgumentException
     * @return \Vivo\Indexer\Query\BooleanInterface
     */
    public function buildTreeQuery($spec)
    {
        if (is_string($spec)) {
            $path   = $spec;
        } elseif ($spec instanceof Entity) {
            /** @var $spec Entity */
            $path   = $spec->getPath();
        } else {
            throw new Exception\InvalidArgumentException(sprintf('%s: Unsupported specification type', __METHOD__));
        }
        $entityTerm          = new IndexerTerm($path, '\path');
        $entityQuery         = new TermQuery($entityTerm);
        $descendantPattern   = new IndexerTerm($path . '/*', '\path');
        $descendantQuery     = new WildcardQuery($descendantPattern);
        $boolQuery           = new BooleanOr($entityQuery, $descendantQuery);
        return $boolQuery;
    }

    /**
     * Builds a term identifying the $entity
     * @param \Vivo\CMS\Model\Entity $entity
     * @return \Vivo\Indexer\Term
     */
    public function buildEntityTerm(Entity $entity)
    {
        return new IndexerTerm($entity->getUuid(), '\uuid');
    }
}
