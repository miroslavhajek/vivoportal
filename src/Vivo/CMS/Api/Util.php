<?php
namespace Vivo\CMS\Api;

use Vivo\Service\EntityProcessorInterface;
use Vivo\CMS\Model\Entity;
use Vivo\CMS\Api\CMS as CmsApi;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;

/**
 * Util
 * Util API
 */
class Util
{
    /**
     * Event triggered before processing an entity
     */
    const EVENT_PROCESS_PRE     = 'process_pre';

    /**
     * Event triggered after processing an entity
     */
    const EVENT_PROCESS_POST    = 'process_post';

    /**
     * CMS API
     * @var CmsApi
     */
    protected $cmsApi;

    /**
     * Event Manager
     * @var EventManagerInterface
     */
    private $eventManager;

    public function __construct(CmsApi $cmsApi)
    {
        $this->cmsApi   = $cmsApi;
    }

    /**
     * Crawls entity subtree starting from the submitted entity and processes the found entities
     * Returns number of crawled entities
     * @param Entity $entity
     * @param EntityProcessorInterface $entityProcessor
     * @return int Number of crawled entities
     */
    public function crawl(Entity $entity, EntityProcessorInterface $entityProcessor)
    {
        $eventManager   = $this->getEventManager();
        $eventParams    = array(
            'entity'    => $entity,
            'success'   => null,
        );
        $eventManager->trigger(self::EVENT_PROCESS_PRE, $this, $eventParams);
        $success    = $entityProcessor->processEntity($entity);
        $eventParams['success'] = $success;
        $eventManager->trigger(self::EVENT_PROCESS_POST, $this, $eventParams);
        $crawled    = 1;
        $children   = $this->cmsApi->getChildren($entity);
        foreach ($children as $child) {
            $crawled += $this->crawl($child, $entityProcessor);
        }
        return $crawled;
    }

    /**
     * Sets the event manager
     * @param EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Returns Event Manager
     * @return EventManager|EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->eventManager = new EventManager();
        }
        return $this->eventManager;
    }
}
