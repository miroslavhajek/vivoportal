<?php
namespace Vivo\UI;

use Vivo\UI\ComponentInterface;

use Zend\EventManager\Event;

/**
 * ComponentEvent
 * Event triggered by UI components
 */
class ComponentEvent extends Event implements ComponentEventInterface
{
    /**
     * Returns component this event belongs to
     * @return ComponentInterface
     */
    public function getComponent()
    {
        return $this->getTarget();
    }
}
