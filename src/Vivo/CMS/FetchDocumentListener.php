<?php
namespace Vivo\CMS;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * Simple listener for fetching document.
 *
 * This listener find document by its path.
 */
class FetchDocumentListener implements ListenerAggregateInterface
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
     * @param \Vivo\CMS\Api\CMS $cmsApi
     */
    public function __construct(Api\CMS $cmsApi)
    {
        $this->cmsApi = $cmsApi;
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
     * Fetch document by requested path.
     * @param \Vivo\CMS\Event\CMSEvent $e
     * @return null|Model\Document
     */
    public function fetchDocument(Event\CMSEvent $e)
    {
        try {
            $document = $this->cmsApi->getSiteEntity($e->getRequestedPath(), $e->getSite());
        } catch (\Vivo\Repository\Exception\EntityNotFoundException $e) {
            return null;
        }

        return $document;
    }
}
