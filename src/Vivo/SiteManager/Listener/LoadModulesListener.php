<?php
namespace Vivo\SiteManager\Listener;

use Vivo\SiteManager\Event\SiteEventInterface;
use Vivo\SiteManager\Exception;
use Vivo\Module\ModuleManagerFactory;
use Vivo\Module\StorageManager\StorageManager as ModuleStorageManager;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use Zend\Di\Config as DiConfig;
use Zend\ServiceManager\Config as SmConfig;

/**
 * SiteResolveListener
 */
class LoadModulesListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Module manager factory
     * @var ModuleManagerFactory
     */
    protected $moduleManagerFactory;

    /**
     * Application's service manager
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Module Storage Manager
     * @var ModuleStorageManager
     */
    protected $moduleStorageManager;

    /**
     * Constructor
     * @param \Vivo\Module\ModuleManagerFactory $moduleManagerFactory
     * @param \Zend\ServiceManager\ServiceManager $sm
     * @param ModuleStorageManager $moduleStorageManager
     */
    public function __construct(ModuleManagerFactory $moduleManagerFactory,
                                ServiceManager $sm,
                                ModuleStorageManager $moduleStorageManager)
    {
        $this->moduleManagerFactory = $moduleManagerFactory;
        $this->serviceManager       = $sm;
        $this->moduleStorageManager = $moduleStorageManager;
    }

    /**
     * Attach to an event manager
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(SiteEventInterface::EVENT_LOAD_MODULES, array($this, 'onLoadModules'));
    }

    /**
     * Detach all our listeners from the event manager
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Listen to "load_modules" event, create the module mgr, load modules, merge config, save module manager
     * @param SiteEventInterface $e
     * @return void
     */
    public function onLoadModules(SiteEventInterface $e)
    {
        $moduleNames    = $e->getModules();
        $this->addMissingDependencies($moduleNames);
        //Create module manager
        $moduleManager  = $this->moduleManagerFactory->getModuleManager($moduleNames);
        $e->setModuleManager($moduleManager);
        //Load modules
        $moduleManager->loadModules();
        //Merge modules config with the site config (site config overrides the modules config)
        $modulesConfig  = $moduleManager->getEvent()->getConfigListener()->getMergedConfig(false);
        $siteConfig     = $e->getSiteConfig();
        if (!$siteConfig) {
            $siteConfig = array();
        }
        //Merge site config into the modules config and use it as site config
        $siteConfig = ArrayUtils::merge($modulesConfig, $siteConfig);
        $e->setSiteConfig($siteConfig);
        //Merge site config into the main config's 'cms' namespace
        $mainConfig = $this->serviceManager->get('config');
        $cmsConfig = $mainConfig['cms'];
        $cmsConfig = ArrayUtils::merge($cmsConfig, $siteConfig);
        //Set 'cms' namespace in the main config to an empty array - the CMS config is accessible via cms_config service
        $mainConfig['cms'] = array();
        $this->serviceManager->setService('config', $mainConfig);
        $this->serviceManager->setService('cms_config', $cmsConfig);
        //Prepare Vivo service manager
        $this->initializeVivoServiceManager($cmsConfig);
        //Prepare Vivo controller loader
        $this->initializeVivoControllerLoader($cmsConfig);
        //Prepare Vivo view helpers manager
        $this->initializeVivoViewHelperManager($cmsConfig);
        $e->stopPropagation(true);
    }

    /**
     * Initialize vivo service manager
     * @param array $cmsConfig
     */
    protected function initializeVivoServiceManager(array $cmsConfig)
    {
        //disable overriding - modules & sites are not supposed to override existing services
        $this->serviceManager->setAllowOverride(false);
        $smConfig = new SmConfig($cmsConfig['service_manager']);
        $smConfig->configureServiceManager($this->serviceManager);
    }

    /**
     * Initialize Vivo view helper manager
     * @param array $cmsConfig
     */
    protected function initializeVivoViewHelperManager(array $cmsConfig)
    {
        if (isset($cmsConfig['view_helpers'])) {
            $viewHelperConfig   = new SmConfig($cmsConfig['view_helpers']);
            /** @var $viewHelperManager \Zend\View\HelperPluginManager */
            $viewHelperManager  = $this->serviceManager->get('view_helper_manager');

            //TODO - check: Do we really want to disable view helper overriding?
            //Disable overriding - modules & sites are not supposed to override existing view helpers (?)
            $viewHelperManager->setAllowOverride(false);

            $viewHelperConfig->configureServiceManager($viewHelperManager);
        }
    }

    /**
     * Initialize Vivo controller loader
     * @param array $cmsConfig
     */
    protected function initializeVivoControllerLoader(array $cmsConfig)
    {
        if (isset($cmsConfig['controllers'])) {
            $controllerConfig   = new SmConfig($cmsConfig['controllers']);
            /** @var $controllerLoader \Zend\Mvc\Controller\ControllerManager */
            $controllerLoader  = $this->serviceManager->get('controller_loader');
            //Disable overriding - modules & sites are not supposed to override existing controllers
            $controllerLoader->setAllowOverride(false);
            $controllerConfig->configureServiceManager($controllerLoader);
        }
    }

    /**
     * Adds missing dependencies to the list of modules
     * @param array $modules
     */
    protected function addMissingDependencies(array &$modules)
    {
        reset($modules);
        while ($module = current($modules)) {
            $dependencies   = $this->getModuleDependencies($module);
            foreach ($dependencies as $dependency) {
                $modules[]  = $dependency;
            }
            next($modules);
        }
    }

    /**
     * Returns an array of module names - dependencies of $module
     * If there are no dependencies, returns an empty array
     * @param string $module
     * @return array
     */
    protected function getModuleDependencies($module)
    {
        $moduleInfo = $this->moduleStorageManager->getModuleInfo($module);
        if (isset($moduleInfo['descriptor']['require'])) {
            $dependencies   = array_keys($moduleInfo['descriptor']['require']);
        } else {
            $dependencies   = array();
        }
        return $dependencies;
    }
}
