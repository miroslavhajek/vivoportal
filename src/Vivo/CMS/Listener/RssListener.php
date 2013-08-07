<?php
namespace Vivo\CMS\Listener;

use Vivo\CMS\Event\CMSEvent;
use Vivo\CMS\UI\Rss;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\PhpEnvironment\Request;

class RssListener implements ListenerAggregateInterface
{
    /**
     * @var \Vivo\CMS\UI\Rss
     */
    private $rssUi;

    /**
     * @var \Zend\Http\PhpEnvironment\Request
     */
    private $request;

    /**
     * @param \Zend\Http\PhpEnvironment\Request $request
     */
    public function __construct(Rss $rssUi, Request $request)
    {
        $this->rssUi = $rssUi;
        $this->request = $request;
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
        if($this->request->getUri()->getQuery() == 'rss') {
            $this->rssUi->setCmsEvent($e);
            $this->rssUi->setDocument($e->getDocument());

            return $this->rssUi;
        }

        return null;
    }

}
