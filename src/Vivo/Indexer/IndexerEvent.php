<?php
namespace Vivo\Indexer;

use Vivo\CMS\Model\Entity;
use Vivo\Indexer\DocumentInterface as IndexerDocInterface;

use Zend\EventManager\Event;

use Exception;

/**
 * IndexerEvent
 */
class IndexerEvent extends Event
{
    /**
     * Indexer events
     */
    const EVENT_INDEX_PRE       = 'index.pre';
    const EVENT_INDEX_POST      = 'index.post';
    const EVENT_INDEX_FAILED    = 'index_failed';

    /**
     * Entity
     * @var Entity
     */
    protected $entity;

    /**
     * Indexer document
     * @var IndexerDocInterface
     */
    protected $idxDoc;

    /**
     * Entity path
     * @var string
     */
    protected $entityPath;

    /**
     * Last exception
     * @var Exception
     */
    protected $exception;

    /**
     * Sets entity
     * @param \Vivo\CMS\Model\Entity $entity
     */
    public function setEntity(Entity $entity = null)
    {
        $this->entity = $entity;
    }

    /**
     * Returns entity
     * @return \Vivo\CMS\Model\Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Sets indexer document
     * @param IndexerDocInterface $idxDoc
     */
    public function setIdxDoc(IndexerDocInterface $idxDoc = null)
    {
        $this->idxDoc = $idxDoc;
    }

    /**
     * Returns indexer document
     * @return \Vivo\Indexer\DocumentInterface
     */
    public function getIdxDoc()
    {
        return $this->idxDoc;
    }

    /**
     * Sets entity path
     * @param string $entityPath
     */
    public function setEntityPath($entityPath)
    {
        $this->entityPath = $entityPath;
    }

    /**
     * Returns entity path
     * @return string
     */
    public function getEntityPath()
    {
        if ($this->entity) {
            return $this->entity->getPath();
        }
        return $this->entityPath;
    }

    /**
     * Sets the last exception
     * @param \Exception $exception
     */
    public function setException(Exception $exception = null)
    {
        $this->exception = $exception;
    }

    /**
     * Returns the last exception
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
