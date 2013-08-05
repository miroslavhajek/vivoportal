<?php
namespace Vivo\UI;

use Vivo\UI\Exception\ComponentNotExists;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ModelInterface;

/**
 * @todo implement ArrayAccess?
 */

class ComponentContainer extends Component implements ComponentContainerInterface
{

    /**
     * Child components
     * @var ComponentInterface[]
     */
    protected $components = array();

    /**
     * Returns child component by name
     * @param string $name
     * @return ComponentInterface
     */
    public function __get($name)
    {
        if ($this->hasComponent($name)) {
            return $this->getComponent($name);
        }
        return $this->$name; //notice if property doesn't exist
    }

    /**
     * Checks if a component with the given name exists
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->hasComponent($name);
    }

    /**
     * Removes the specified component
     * @param string $name
     */
    public function __unset($name)
    {
        if ($this->hasComponent($name))
            $this->removeComponent($name);
    }

    /**
     * Sets child component
     * @param string $name
     * @param mixed $object
     */
    public function __set($name, $object)
    {
        if ($object instanceof ComponentInterface) {
            $this->addComponent($object, $name);
        } else {
            $this->$name = $object;
        }
    }

    /* (non-PHPdoc)
     * @see Vivo\UI.ComponentContainerInterface::addComponent()
     */
    public function addComponent(ComponentInterface $component, $name)
    {
        //TODO check cycles in component tree
        $eventManager   = $this->getEventManager();
        $event          = $this->getEvent();
        $event->setParam('addComponent', array(
            'name' => $name,
            'component' => $component
        ));
        $event->setParam('log', array(
            'message'   => sprintf("Component '%s' will be added under name '%s'", get_class($component), $name),
            'priority'  => \VpLogger\Log\Logger::DEBUG,
        ));
        $eventManager->trigger(self::EVENT_COMPONENT_ADD_PRE, $event);
        $component->setParent($this, $name);
        $this->components[$name] = $component;
        $event->setParam('log', array(
            'message'   => sprintf("Component '%s' was added under name '%s'", get_class($component), $name),
            'priority'  => \VpLogger\Log\Logger::DEBUG,
        ));
        $eventManager->trigger(self::EVENT_COMPONENT_ADD_POST, $event);
    }

    /**
     * Adds multiple components at once
     * @param ComponentInterface[] $components
     */
    public function addComponents(array $components)
    {
        foreach ($components as $name => $component) {
            $this->addComponent($component, $name);
        }
    }

    /* (non-PHPdoc)
     * @see Vivo\UI.ComponentContainerInterface::removeComponent()
     */
    public function removeComponent($name)
    {
        //TODO also accept component object as parameter
        $eventManager   = $this->getEventManager();
        $event          = $this->getEvent();
        $event->setParam('removeComponent', array('name' => $name, 'component' => null));
        $event->setParam('log', array(
            'message'   => sprintf("Component under name '%s' will be removed", $name),
            'priority'  => \VpLogger\Log\Logger::DEBUG,
        ));
        $eventManager->trigger(self::EVENT_COMPONENT_REMOVE_PRE, $event);

        if (!$this->hasComponent($name)) {
            throw new ComponentNotExists(
                "Component `$name` doesn't exist in container `"
                    . $this->getPath() . "`.");
        }
        $component  = $this->getComponent($name);
        $component->setParent(null, null);
        unset($this->components[$name]);
        $event->setParam('removeComponent', array('name' => $name, 'component' => $component));
        $event->setParam('log', array(
            'message'   => sprintf("Component '%s' registered under name '%s' was removed",
                get_class($component), $name),
            'priority'  => \VpLogger\Log\Logger::DEBUG,
        ));
        $eventManager->trigger(self::EVENT_COMPONENT_REMOVE_POST, $event);
    }

    public function removeAllComponents()
    {
        foreach (array_keys($this->components) as $name) {
            $this->removeComponent($name);
        }
    }

    /**
     * Returns component by name
     * @param string $name
     * @return ComponentInterface
     */
    public function getComponent($name)
    {
        if (!$this->hasComponent($name)) {
            throw new ComponentNotExists(
                "Component `$name` doesn't exist in container `"
                    . $this->getPath() . "`.");
        }
        return $this->components[$name];
    }

    /**
     * Returns components.
     * @return array
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasComponent($name)
    {
        return isset($this->components[$name]);
    }

    /**
     * Listener for ComponentEventInterface::EVENT_VIEW event
     * @param ComponentEventInterface $event
     * @throws Exception\RuntimeException
     */
    public function viewListenerChildViews(ComponentEventInterface $event)
    {
        if ($event->getComponent() != $this) {
            //This view listener expects only this component as target
            throw new Exception\RuntimeException(sprintf("%s: Unexpected component", __METHOD__));
        }
        $viewModel = $this->getView();
        foreach ($this->components as $name => $component) {
            $model = $component->getView();
            if ($model instanceof ModelInterface) {
                $viewModel->addChild($model, $name);
            } else {
                $viewModel->setVariable($name, $model);
            }
        }
        $container = array (
            'components' => array_keys($this->components),
        );
        $viewModel->setVariable('container', $container);
    }

    /**
     * Triggers early init event on all children components
     * @param ComponentEventInterface $event
     * @throws Exception\RuntimeException
     */
    public function initEarlyListenerInitChildren(ComponentEventInterface $event)
    {
        if ($event->getComponent() != $this) {
            //This view listener expects only this component as target
            throw new Exception\RuntimeException(sprintf("%s: Unexpected component", __METHOD__));
        }
        foreach ($this->components as $component) {
            $componentEvents    = $component->getEventManager();
            $componentEvent     = $component->getEvent();
            $componentEvents->trigger(ComponentEventInterface::EVENT_INIT_EARLY, $componentEvent);
        }
    }

    /**
     * Triggers init event on all children components
     * @param ComponentEventInterface $event
     * @throws Exception\RuntimeException
     */
    public function initListenerInitChildren(ComponentEventInterface $event)
    {
        if ($event->getComponent() != $this) {
            //This view listener expects only this component as target
            throw new Exception\RuntimeException(sprintf("%s: Unexpected component", __METHOD__));
        }
        foreach ($this->components as $component) {
            $componentEvents    = $component->getEventManager();
            $componentEvent     = $component->getEvent();
            $componentEvents->trigger(ComponentEventInterface::EVENT_INIT, $componentEvent);
        }
    }

    /**
     * Triggers late init event on all children components
     * @param ComponentEventInterface $event
     * @throws Exception\RuntimeException
     */
    public function initLateListenerInitChildren(ComponentEventInterface $event)
    {
        if ($event->getComponent() != $this) {
            //This view listener expects only this component as target
            throw new Exception\RuntimeException(sprintf("%s: Unexpected component", __METHOD__));
        }
        foreach ($this->components as $component) {
            $componentEvents    = $component->getEventManager();
            $componentEvent     = $component->getEvent();
            $componentEvents->trigger(ComponentEventInterface::EVENT_INIT_LATE, $componentEvent);
        }
    }

    /**
     * Attaches listeners
     * @return void
     */
    public function attachListeners()
    {
        parent::attachListeners();
        $eventManager   = $this->getEventManager();
        //Init early
        $this->listeners['initEarlyListenerInitChildren']
            = $eventManager->attach(ComponentEventInterface::EVENT_INIT_EARLY,
            array($this, 'initEarlyListenerInitChildren'), -10000);
        //Init
        $this->listeners['initListenerInitChildren']
            = $eventManager->attach(ComponentEventInterface::EVENT_INIT,
            array($this, 'initListenerInitChildren'), -10000);
        //Init late
        $this->listeners['initLateListenerInitChildren']
            = $eventManager->attach(ComponentEventInterface::EVENT_INIT_LATE,
            array($this, 'initLateListenerInitChildren'), -10000);
        //View
        $this->listeners['viewListenerChildViews']
            = $eventManager->attach(ComponentEventInterface::EVENT_VIEW,
            array($this, 'viewListenerChildViews'));
    }
}
