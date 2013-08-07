<?php
namespace Vivo\CMS\Listener;

use Vivo\CMS\Api;
use Vivo\CMS\Api\Exception\DocumentNotFoundException;
use Vivo\CMS\Event\CMSEvent;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use VpLogger\Log\Logger;

/**
 * Default listener for fetching error documents.
 *
 * This listener uses a config to map an errorcode to an error document path.
 */
class FetchErrorDocumentListener implements ListenerAggregateInterface
{

    /**
     * Indexer api
     * @var \Vivo\CMS\Api\Document
     */
    protected $documentApi;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Configuration.
     * @example array ('codes' => array('404' => '/error-404/'), 'default'=> '/error/')
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     * @param \Vivo\CMS\Api\Document $cmsApi
     * @param array $config
     */
    public function __construct(Api\Document $documentApi, array $config)
    {
        $this->documentApi = $documentApi;
        $this->config = $config;
    }

    /**
     * Attach to an event manager.
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(CMSEvent::EVENT_FETCH_ERRORDOCUMENT, array($this, 'fetchDocument'));
    }

    /**
     * Detach all our listeners from the event manager.
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
     * Fetch error document.
     * @param \Vivo\CMS\Event\CMSEvent $cmsEvent
     * @return null|Model\Document
     */
    public function fetchDocument(CMSEvent $cmsEvent)
    {
        $document = null;
        $docPath = array();
        $exceptionCode = $cmsEvent->getException()->getCode();
        foreach($this->config['code'] as $code => $path){
            if ($exceptionCode == $code) {
                $docPath[] = $path;
                break;
            }
        }

        if(isset($this->config['default'])) {
            $docPath[] = $this->config['default'];
        }

        $events = new EventManager();

        foreach ($docPath as $path) {
            try {
                $document = $this->documentApi->getSiteDocument($path, $cmsEvent->getSite());
                break;
            } catch (DocumentNotFoundException $e) {
                $events->trigger('log', $this, array(
                        'message'    => sprintf("Error document (%s) not found (%s)", $exceptionCode, $e->getMessage()),
                        'priority'   => Logger::ERR));
            }
        }

        return $document;
    }
}
