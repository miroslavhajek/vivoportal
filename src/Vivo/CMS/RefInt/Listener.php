<?php
namespace Vivo\CMS\RefInt;

use Vivo\Repository\EventInterface as RepositoryEventInterface;
use Vivo\CMS\Model\Entity;
use Vivo\CMS\Api\Exception;

/**
 * Class Listener
 * Listens to the repository events and converts URLs and symbolic references in entities being saved / retrieved
 */
class Listener
{
    /**
     * Symbolic reference convertor
     * @var SymRefConvertorInterface
     */
    protected $symRefConvertor;

    /**
     * Construct
     * @param SymRefConvertorInterface $symRefConvertor
     */
    public function __construct(SymRefConvertorInterface $symRefConvertor)
    {
        $this->symRefConvertor  = $symRefConvertor;
    }

    /**
     * Converts URLs to symbolic references prior to entity serialization in repository
     * @param RepositoryEventInterface $event
     * @throws \Vivo\CMS\Api\Exception\InvalidArgumentException
     */
    public function onRepositorySerializePre(RepositoryEventInterface $event)
    {
        $entity = $event->getParam('entity');
        if (!$entity instanceof Entity) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Parameter 'entity' must be an Entity instance", __METHOD__));
        }
        $this->symRefConvertor->convertUrlsToReferences($entity);
    }

    /**
     * Converts symbolic references to URLs post entity un-serialization in repository
     * @param RepositoryEventInterface $event
     * @throws \Vivo\CMS\Api\Exception\InvalidArgumentException
     */
    public function onRepositoryUnSerializePost(RepositoryEventInterface $event)
    {
        $entity = $event->getParam('entity');
        if (!$entity instanceof Entity) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Parameter 'entity' must be an Entity instance", __METHOD__));
        }
        $this->symRefConvertor->convertReferencesToURLs($entity);
    }
}
