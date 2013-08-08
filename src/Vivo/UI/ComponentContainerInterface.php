<?php
namespace Vivo\UI;

/**
 * Class that implements this interface can contain child components
 */
interface ComponentContainerInterface
{
    /**#@+
     * Events triggered by the component container
     */
    const EVENT_COMPONENT_ADD_PRE       = 'component_add_pre';
    const EVENT_COMPONENT_ADD_POST      = 'component_add_post';
    const EVENT_COMPONENT_REMOVE_PRE    = 'component_remove_pre';
    const EVENT_COMPONENT_REMOVE_POST   = 'component_remove_post';
    /**#@-*/

    /**
     * Adds a component to the container
     * @param ComponentInterface $component
     * @param string $name
     * @return void
     */
    public function addComponent(ComponentInterface $component, $name);

    /**
     * Removes component from the container
     * @param string $name
     * @return void
     */
    public function removeComponent($name);

    /**
     * Returns component by name
     * @param string $name
     * @return ComponentInterface
     */
    public function getComponent($name);

    /**
     * Returns an array of all components in the container
     * @return array
     */
    public function getComponents();

    /**
     * Returns if the container contains component with the specified name
     * @param string $name
     * @return bool
     */
    public function hasComponent($name);
}
