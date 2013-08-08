<?php
namespace Vivo\UI;

use Zend\EventManager\EventInterface;

/**
 * ComponentEventInterface
 * Interface for Component Event
 */
interface ComponentEventInterface extends EventInterface
{
    /**#@+
     * Component events
     */
    const EVENT_INIT_EARLY  = 'init_early';
    const EVENT_INIT        = 'init';
    const EVENT_INIT_LATE   = 'init_late';
    const EVENT_VIEW        = 'view';
    const EVENT_DONE        = 'done';
    /**#@-*/

    /**
     * Returns component this event belongs to
     * @return ComponentInterface
     */
    public function getComponent();
}