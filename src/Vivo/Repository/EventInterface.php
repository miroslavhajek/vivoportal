<?php
namespace Vivo\Repository;

use Zend\EventManager\EventInterface as ZendEventInterface;

/**
 * EventInterface
 * Repository event interface
 */
interface EventInterface extends ZendEventInterface
{
    /**
     * Event triggered right before the repository commit finishes
     */
    const EVENT_COMMIT              = 'commit';

    /**
     * Event triggered right before an entity is serialized prior to saving into storage
     */
    const EVENT_SERIALIZE_PRE       = 'serialize_pre';

    /**
     * Event triggered after an entity is serialized
     */
    const EVENT_UNSERIALIZE_POST    = 'unserialize_post';

    /**
     * Event triggered when getting an entity fails
     */
    const EVENT_GET_FAILED          = 'get_failed';
}
