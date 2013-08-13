<?php
namespace Vivo\UI;

use Vivo\CMS\UI\Component;
use Vivo\UI\Exception\LogicException;
use Vivo\UI\Exception\ExceptionInterface as UIException;
use Vivo\UI\Exception\RuntimeException;
use Vivo\UI\ComponentEventInterface;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Http\PhpEnvironment\Request;

/**
 * Performs operation on UI component tree.
 */
class ComponentTreeController implements EventManagerAwareInterface
{
    /**
     * @var ComponentInterface
     */
    protected $root;

    /**
     * @var \Zend\Session\Container
     */
    protected $session;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \Zend\EventManager\EventManagerInterface
     */
    protected $events;

    /**
     * Constructor.
     * @param SessionManager $sessionManager
     * @param \Zend\Http\PhpEnvironment\Request $request
     */
    public function __construct(SessionManager $sessionManager, Request $request)
    {
        $this->session = new Container('component_states', $sessionManager);
        $this->request = $request;
    }

    /**
     * Sets root component of tree.
     * @param ComponentInterface $root
     */
    public function setRoot(ComponentInterface $root)
    {
        $this->root = $root;
    }

    /**
     * Returns root component of tree.
     * @throws LogicException
     * @return \Vivo\UI\ComponentInterface
     */
    public function getRoot()
    {
        if (null === $this->root) {
            throw new LogicException(
                sprintf('%s: Root component is not set.', __METHOD__));
        }
        return $this->root;
    }


    /**
     * Returns component by its path in tree.
     * @param string $path
     * @throws RuntimeException
     * @return \Vivo\UI\ComponentInterface
     */
    public function getComponent($path)
    {
        $component = $this->getRoot();
        $names = explode(Component::COMPONENT_SEPARATOR, $path);
        for ($i = 1; $i < count($names); $i++) {
            try {
                $component = $component->getComponent($names[$i]);
            } catch (UIException $e) {
                throw new RuntimeException(
                    sprintf("%s: Component for path '%s' not found.",
                        __METHOD__, $path), null, $e);
            }
        }
        return $component;
    }

    /**
     * Initialize component tree
     */
    public function init()
    {
        $this->events->trigger('log:start', $this, array('subject' => 'tree_controller:init'));
        $this->triggerEventOnRootComponent(ComponentEventInterface::EVENT_INIT_EARLY);
        $this->triggerEventOnRootComponent(ComponentEventInterface::EVENT_INIT);
        $this->triggerEventOnRootComponent(ComponentEventInterface::EVENT_INIT_LATE);
        $this->events->trigger('log:stop', $this, array('subject' => 'tree_controller:init'));
    }

    /**
     * Triggers an event on the root component
     * @param string $event
     */
    protected function triggerEventOnRootComponent($event)
    {
        $rootEvents     = $this->root->getEventManager();
        $rootEvent      = $this->root->getEvent();
        $rootEvent->setParams(array(
            'log'   => array(
                'message'   => sprintf("Event '%s' triggered on the root component '%s'",
                                $event, $this->root->getPath()),
                'priority'  => \VpLogger\Log\Logger::PERF_FINER,
            ),
        ));
        $rootEvents->trigger($event, $rootEvent);
    }

    /**
     * Returns an array of components currently present in the component tree
     * @return ComponentInterface[]
     */
    protected function getCurrentComponents()
    {
        $components = array();
        foreach ($this->getTreeIterator() as $component){
            $components[]   = $component;
        }
        return $components;
    }

    /**
     * Creates session key
     * @param \Vivo\UI\ComponentInterface $component
     * @return string
     */
    protected function createSessionKey($component)
    {
        $sessionKey = $this->request->getUri()->getPath() . ':' . $component->getPath();
        return $sessionKey;
    }

    /**
     * Loads state of components.
     */
    public function loadState()
    {
        $this->events->trigger('log:start', $this, array('subject' => 'tree_controller:load_state'));

        foreach ($this->getTreeIterator() as $component){
            if ($component instanceof PersistableInterface){
                $key = $this->createSessionKey($component);
                $state = $this->session[$key];
                $message = 'Load component: ' . $component->getPath(). ', state: ' . implode('--', (array) $state);
                $this->events->trigger('log', $this, array('message' => $message,
                    'priority'=> \VpLogger\Log\Logger::PERF_FINER));
                $component->loadState($state);
            }
        }

        $this->events->trigger('log:stop', $this, array('subject' => 'tree_controller:load_state'));
    }

    /**
     * Save state of components.
     */
    public function saveState()
    {
        $this->events->trigger('log:start', $this, array('subject' => 'tree_controller:save_state'));

        foreach ($this->getTreeIterator() as $component) {
            if ($component instanceof PersistableInterface){
                $state = $component->saveState();
                $key = $this->createSessionKey($component);
                $message = 'Save component: ' . $component->getPath() . ', state: ' . implode('--', (array) $state);
                $this->events->trigger('log', $this, array('message' => $message,
                    'priority'=> \VpLogger\Log\Logger::PERF_FINER));
                $this->session[$key] = $state;
            }
        }
        $this->events->trigger('log:stop', $this, array('subject' => 'tree_controller:save_state'));
    }

    /**
     * Returns view model tree from component tree or a string to display directly
     * @return \Zend\View\Model\ModelInterface|string
     */
    public function view()
    {
        $this->events->trigger('log:start', $this, array('subject' => 'tree_controller:view'));
        /** @var $component ComponentInterface */
        foreach ($this->getTreeIterator() as $component){
            $componentEvents    = $component->getEventManager();
            $componentEvent     = $component->getEvent();
            $componentEvent->setParams(array(
                'log'   => array(
                    'message'   => sprintf('View component: %s', $component->getPath()),
                    'priority'  => \VpLogger\Log\Logger::PERF_FINER,
                ),
            ));
            $componentEvents->trigger(ComponentEventInterface::EVENT_VIEW, $componentEvent);
        }
        $this->events->trigger('log:stop', $this, array('subject' => 'tree_controller:view'));
        return $this->root->getView();
    }

    /**
     * Call done on components.
     */
    public function done()
    {
        $this->events->trigger('log:start', $this, array('subject' => 'tree_controller:done'));
        /** @var $component ComponentInterface */
        foreach ($this->getTreeIterator() as $component){
            $componentEvents    = $component->getEventManager();
            $componentEvent     = $component->getEvent();
            $componentEvent->setParams(array(
                'log'   => array(
                    'message'   => sprintf('Done component: %s', $component->getPath()),
                    'priority'  => \VpLogger\Log\Logger::PERF_FINER,
                ),
            ));
            $componentEvents->trigger(ComponentEventInterface::EVENT_DONE, $componentEvent);
        }
        $this->events->trigger('log:stop', $this, array('subject' => 'tree_controller:done'));
    }

    /**
     * Only init branch of tree defined by path.
     * @param string $path
     */
    public function initComponent($path)
    {
        //TODO
    }

    /**
     * Invokes action on component.
     * @param string $path
     * @param string $action
     * @throws Exception\RuntimeException
     * @param array $params
     * @return mixed
     */
    public function invokeAction($path, $action, $params)
    {
        if (substr($action, 0, 2) == '__' || $action == 'init') // Init and php magic methods are not accessible.
            throw new RuntimeException("Method $action is not accessible");
        $component = $this->getComponent($path);

        if (!method_exists($component, $action)) {
            throw new RuntimeException(
                sprintf("%s: Component '%s' doesn't have method '%s'.",
                    __METHOD__, get_class($component), $action));
        }

        try {
            return call_user_func_array(array($component, $action), $params);
        } catch (UIException $e) {
            throw new RuntimeException(
                sprintf("%s: Can not call action on component.", __METHOD__),
                null, $e);
        }
    }

    /**
     * Returns iterator for iterating over component tree.
     * @param integer $mode
     * @return \RecursiveIteratorIterator
     * @see \RecursiveIteratorIterator for available modes.
     */
    public function getTreeIterator($mode = \RecursiveIteratorIterator::SELF_FIRST)
    {
        $iter  = new ComponentTreeIterator(array($this->getRoot()));
        return new \RecursiveIteratorIterator ($iter, $mode);
    }

    /**
     * Returns event manager.
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * Sets event manager
     * @param \Zend\EventManager\EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events = $eventManager;
        $this->events->addIdentifiers(__CLASS__);
    }
}
