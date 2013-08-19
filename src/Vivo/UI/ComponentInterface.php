<?php
namespace Vivo\UI;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerAwareInterface;

/**
 * Interface for UI components.
 */
interface ComponentInterface extends EventManagerAwareInterface
{
    /**
     * Returns view model of the Component or string to display directly
     * @return \Zend\View\Model\ModelInterface|string
     */
    public function getView();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return ComponentContainerInterface
     */
    public function getParent();

    /**
     * Returns component Event
     * @return ComponentEventInterface
     */
    public function getEvent();

    /**
     * Attaches listeners
     * @return void
     */
    public function attachListeners();
}
