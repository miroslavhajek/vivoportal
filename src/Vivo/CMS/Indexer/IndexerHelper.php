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
use Vivo\CMS\Model\Content;

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
     * Constructor
     * @param FieldHelperInterface $indexerFieldHelper
     */
    public function __construct(FieldHelperInterface $indexerFieldHelper)
    {
        $this->indexerFieldHelper   = $indexerFieldHelper;
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
