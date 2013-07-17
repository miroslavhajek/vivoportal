<?php
namespace Vivo\UI;

use Vivo\CMS\UI\Component;
use Vivo\UI\Exception\LogicException;
use Vivo\UI\Exception\ExceptionInterface as UIException;
use Vivo\UI\Exception\RuntimeException;

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
     * Initialize component tree.
     */
    public function init()
    {
        $components = $this->getCurrentComponents();
        foreach ($components as $component) {
            //A component might have been removed from the tree in an init() (as is the case with ResourceEditor
            //=> check the component is still in the tree, if not, do not try to initialize it
            //TODO - Optimization? This is n^2, as all components are read for every component
            $currentComponents  = $this->getCurrentComponents();
            if (in_array($component, $currentComponents)) {
                $message = 'Init component: ' . $component->getPath();
                $this->events->trigger('log', $this, array('message' => $message,
                    'priority'=> \VpLogger\Log\Logger::PERF_FINER)
                );
                $component->init();
            }
        }
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
    }

    /**
     * Save state of components.
     */
    public function saveState()
    {
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
    }

    /**
     * Returns view model tree from component tree.
     * @return Ambigous <\Zend\View\Model\ModelInterface, string>
     */
    public function view()
    {
        return $this->root->view();
    }

    /**
     * Call done on components.
     */
    public function done()
    {
        foreach ($this->getTreeIterator() as $component){
            $component->done();
        }
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
