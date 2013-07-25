<?php
namespace Vivo\CMS;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * Simple listener for fetching document.
 *
 * This listener find document by url property.
 */
class FetchDocumentByUrlListener implements ListenerAggregateInterface
{

    /**
     * @var Api\Indexer
     */
    protected $indexerApi;
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Constructor.
     * @param \Vivo\CMS\Api\Indexer $indexerApi
     */
    public function __construct(Api\Indexer $indexerApi)
    {
        $this->indexerApi = $indexerApi;
    }

    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event\CMSEvent::EVENT_FETCH_DOCUMENT, array($this, 'fetchDocument'));
    }

    /**
     * Detach all our listeners from the event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Fetch document by url property.
     * @param \Vivo\CMS\Event\CMSEvent $e
     * @return Model\Document | null
     */
    public function fetchDocument(Event\CMSEvent $e)
    {
        $query = sprintf('\Vivo\CMS\Model\Document\uri:"%s" AND \class:"Vivo\CMS\Model\Document"',
                $e->getRequestedPath());
        $documents = $this->indexerApi->getEntitiesByQuery($query);
        return current($documents)?:null;
    }
}
