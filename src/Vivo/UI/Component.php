<?php
namespace Vivo\UI;

use Vivo\UI\ComponentEventInterface;
use Vivo\UI\ComponentEvent;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Base class for UI components.
 *
 */
class Component implements ComponentInterface
{
    /**
     * String used to separate component names in path
     * @var string
     */
    const COMPONENT_SEPARATOR = '->';

    /**
     * Component parent.
     * @var \Vivo\UI\ComponentInterface
     */
    private $parent;

    /**
     * Component name.
     * @var string
     */
    private $name;

    /**
     *
     * @var ModelInterface
     */
    protected $view;

    /**
     * Component Event manager
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * Component Event
     * @var ComponentEventInterface
     */
    private $event;

    /**
     * Array of attached listeners
     * @var array
     */
    protected $listeners;

    /**
     * @param ModelInterface $view
     */
    public function setView(ModelInterface $view)
    {
        $this->view = $view;
    }

    /**
     * Returns view model of the Component or string to display directly
     * @return \Zend\View\Model\ModelInterface|string
     */
    public function getView()
    {
        if ($this->view === null) {
            $this->view = new ViewModel();
        }
        return $this->view;
    }

    /**
     * Initialize the view model on the ComponentEventInterface::EVENT_VIEW event
     */
    public function viewListenerInitView()
    {
        $view   = $this->getView();
        if ($view instanceof ModelInterface) {
            if ($view->getTemplate() == '') {
                $view->setTemplate($this->getDefaultTemplate());
            }
            $component = array(
                'component' => $this,
                'path' => $this->getPath(),
                'name' => $this->getName(),
            );
            $view->setVariable('component', $component);
        }
    }

    /**
     * Returns view model or string to display directly
     * This method is present for backward compatibility - many existing descendants call parent::view() to obtain
     * the view model
     * @return \Zend\View\Model\ModelInterface|string
     */
    public function view()
    {
        return $this->getView();
    }

    /**
     * Returns path to this component
     * @param string $path
     * @return string
     */
    public function getPath($path = '')
    {
        $component = $this;
        while ($component) {
            $name = $component->getName();
            $path = $path ? ($name ? $name . self::COMPONENT_SEPARATOR : $name)
                    . $path : $name;
            $component = $component->getParent();
        }
        return $path;
    }

    /**
     * For backward compatibility only
     * Many existing extending components call parent::init()
     */
    public function init()
    {
    }

    /**
     * For backward compatibility only
     * Existing extending components might call parent::done()
     */
    public function done()
    {
    }

    /**
     * Returns default template name
     * @return string
     */
    public function getDefaultTemplate()
    {
        return get_class($this);
    }

    /**
     * Return parent of component in component tree.
     * @param string|null $className
     * @return ComponentContainerInterface
     */
    public function getParent($className = null)
    {
        $parent = $this->parent;
        if ($className) {
            while ($parent && !$parent instanceof $className) {
                $parent = $parent->getParent();
            }
        }
        return $parent;
    }

    /**
     * Sets parent component
     * This method should be called only from ComponentContainer::addComponent()
     * @param ComponentContainerInterface $parent
     * @param string $name
     */
    public function setParent(ComponentContainerInterface $parent = null, $name)
    {
        $this->parent = $parent;
        if ($name) {
            $this->setName($name);
        }
    }

    /**
     * Returns name of this component
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name of this component
     * @param string $name
     */
    public function setName($name = null)
    {
        //TODO check name format (alfanum)
        $this->name = $name;
    }

    /**
     * Returns component Event Manager
     * @return \Zend\EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * Sets the component Event Manager
     * @param \Zend\EventManager\EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->addIdentifiers($this->getEventManagerIdentifiers());
        $this->eventManager = $eventManager;
    }

    /**
     * Returns an array of identifiers for the EventManager of this component
     * @return array
     */
    protected function getEventManagerIdentifiers()
    {
        $identifiers    = array(
            __CLASS__,
            get_class($this),
        );
        return $identifiers;
    }

    /**
     * Returns component Event
     * @return ComponentEventInterface
     */
    public function getEvent()
    {
        if (!$this->event) {
            $this->event    = new ComponentEvent();
            $this->event->setTarget($this);
        }
        return $this->event;
    }

    /**
     * Attaches listeners
     * @return void
     */
    public function attachListeners()
    {
        $eventManager   = $this->getEventManager();
        //View
        $this->listeners['viewListenerInitView']
            = $eventManager->attach(ComponentEventInterface::EVENT_VIEW,
                array($this, 'viewListenerInitView'));
    }
}
