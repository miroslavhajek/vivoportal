<?php
namespace Vivo\CMS;

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
 * <status_code> <source> <target>
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
        $this->listeners[] = $events->attach(Event\CMSEvent::EVENT_REDIRECT, array($this, 'redirect'));
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
    public function redirect(Event\CMSEvent $cmsEvent)
    {
        try {
            $redirectMap = $this->cmsApi->getResource($cmsEvent->getSite(), 'redirect.map');
        } catch (\Exception $e){
            //TODO: CMSApi should returns a concrete exception when the resoure does not exist.
            return;
        }
        $lines  = explode("\n", $redirectMap);

        //parse redirect map file
        foreach ($lines as $line) {
            if ($line = trim($line)) { //skip empty rows
                $lineColums = array_values(array_filter(explode(" ", $line)));
                if (count($lineColums) == 3) {
                    list($code , $source, $target) = $lineColums;
                    if ($cmsEvent->getRequestedPath() == $source) {
                        $params = array('status_code' => $code);
                        $this->events->trigger(new RedirectEvent($target, $params));
                        $cmsEvent->stopPropagation();
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
