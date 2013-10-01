<?php
namespace Vivo\CMS\Listener;

use Vivo\CMS\Api;
use Vivo\CMS\Event\CMSEvent;
use Vivo\Util\RedirectEvent;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * Listener for redirect map.
 *
 * This listener loads redirect.map (a site resource file), parses it and performs redirect.
 *
 * @example format of redirect.map file:
 * <source> <target>[ <status_code>]
 */
class RedirectMapListener implements ListenerAggregateInterface, EventManagerAwareInterface
{

    /**
     * @var Api\CMS
     */
    protected $cmsApi;

    /**
     * @var EventManagerInterface
     */
    protected $events;

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
        $this->listeners[] = $events->attach(CMSEvent::EVENT_REDIRECT, array($this, 'redirect'));
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
     * Redirect.
     * @param \Vivo\CMS\Event\CMSEvent $e
     * @return null|Model\Document
     */
    public function redirect(CMSEvent $cmsEvent)
    {
        try {
            $redirectMap = $this->cmsApi->getResource($cmsEvent->getSite(), 'redirect.map');
        } catch (\Exception $e){
            //TODO: CMSApi should returns a concrete exception when the resoure does not exist.
            return;
        }

        //parse redirect map file
        $lines  = explode("\n", $redirectMap);
        foreach ($lines as $line) {
            if ($line = trim($line)) { //skip empty rows
                $lineColums = array_values(array_filter(explode(' ', $line)));
                $lineColumsCount = count($lineColums);
                if ($lineColumsCount == 2 || $lineColumsCount == 3) {
                    list($source, $target, $code) = array_merge($lineColums, array(null, null, null));
                    if ($cmsEvent->getRequestedPath() == $source) {
                        $cmsEvent->stopPropagation();
                        $this->events->trigger(new RedirectEvent($target, array('status_code' => $code)));
                        return null;
                    }
                }
            }
        }
    }

    /**
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
       return $this->events;
    }

    /**
     * Sets event manager
     * @param EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events = $eventManager;
        $this->events->addIdentifiers(__CLASS__);
    }
}
