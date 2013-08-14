<?php
namespace Vivo\CMS\Listener;

use Vivo\CMS\ComponentFactory;
use Vivo\CMS\Event\CMSEvent;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;

class ComponentTreeFromDocumentListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @param \Vivo\CMS\ComponentFactory $componentFactory
     */
    public function __construct(ComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(CMSEvent::EVENT_CREATE, array($this, 'invoke'), $priority);
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function invoke(CMSEvent $e)
    {
        $component  = $this->componentFactory->getRootComponent($e->getDocument());
        return $component;
    }
}
