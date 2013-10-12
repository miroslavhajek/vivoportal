<?php
namespace Vivo\Module;

use Vivo\Module\ModuleManager;
use Vivo\Module\Exception;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\ModuleManager\Listener\ModuleResolverListener;
use Zend\ModuleManager\Listener\AutoloaderListener;
use Zend\ModuleManager\Listener\InitTrigger;
use Zend\ModuleManager\Listener\ConfigListener;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ModuleManagerFactory
 * Factory class for Module manager
 */
class ModuleManagerFactory
{
    /**
     * Paths to modules
     * @var array
     */
    protected $modulePaths = array();

    /**
     * Stream name for Vmodule access
     * @var string
     */
    protected $moduleStreamName;

    /**
     * Application's event manager
     * @var EventManagerInterface
     */
    protected $appEvents;

    /**
     * Main Service locator
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Constructor
     * @param array $modulePaths Absolute path in Storage
     * @param string $moduleStreamName
     * @param EventManagerInterface $appEvents
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $modulePaths,
                                $moduleStreamName,
                                EventManagerInterface $appEvents,
                                ServiceLocatorInterface $serviceLocator)
    {
        if (!$moduleStreamName) {
            throw new Exception\InvalidArgumentException(sprintf('%s: Module stream name not set', __METHOD__));
        }
        $this->modulePaths      = $modulePaths;
        $this->moduleStreamName = $moduleStreamName;
        $this->appEvents        = $appEvents;
        $this->serviceLocator   = $serviceLocator;
    }

    /**
     * Creates and returns a new Vmodule manager instance
     * @param array $moduleNames
     * @return \Zend\ModuleManager\ModuleManager
     */
    public function getModuleManager(array $moduleNames)
    {
        $events             = new EventManager();
        $moduleAutoloader   = new AutoloaderModule($this->modulePaths, $this->moduleStreamName);
        $configListener     = new ConfigListener();

        // High priority
        $events->attach(ModuleEvent::EVENT_LOAD_MODULES, array($moduleAutoloader, 'register'), 9000);
        $events->attach(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, new ModuleResolverListener());
        // High priority
        $events->attach(ModuleEvent::EVENT_LOAD_MODULE, new AutoloaderListener(), 9000);
        $events->attach(ModuleEvent::EVENT_LOAD_MODULE, new InitTrigger());

        //OnBootstrapListener would be only useful if the Site was refactored to have a bootstrap process,
        //the Vmodule onBootstrap() method could then be called on the Site bootstrap
//        $events->attach(ModuleEvent::EVENT_LOAD_MODULE, new \Zend\ModuleManager\Listener\OnBootstrapListener($options));
        //LocatorRegistrationListener is not needed (registers the module with the service manager)
        //$events->attach($locatorRegistrationListener);

        $events->attach($configListener);
        $moduleManager  = new ModuleManager($moduleNames, $events);
        $moduleManager->setAppEventManager($this->appEvents);
        $moduleEvent    = new ModuleEvent();
        $moduleEvent->setServiceLocator($this->serviceLocator);
        $moduleManager->setEvent($moduleEvent);
        return $moduleManager;
    }
}
