<?php
namespace Vivo\SiteManager;

use Vivo\SiteManager\Event\SiteEventInterface;
use Vivo\SiteManager\Exception;
use Vivo\SiteManager\Listener\LoadModulesPostListener;
use Vivo\SiteManager\Listener\SiteModelLoadListener;
use Vivo\SiteManager\Listener\SiteConfigListener;
use Vivo\SiteManager\Listener\BackendConfigListener;
use Vivo\SiteManager\Listener\LoadModulesListener;
use Vivo\SiteManager\Listener\CollectSiteModulesListener;
use Vivo\SiteManager\Listener\CollectBackendModulesListener;
use Vivo\SiteManager\Listener\InjectModuleManagerListener;
use Vivo\SiteManager\Listener\InjectSecurityManagerListener;
use Vivo\SiteManager\Listener\ReconfigureForBackendListener;
use Vivo\Module\ModuleManagerFactory;
use Vivo\Module\StorageManager\StorageManager as ModuleStorageManager;
use Vivo\Module\ResourceManager\ResourceManager as ModuleResourceManager;
use Vivo\CMS\Api\Site as SiteApi;
use Vivo\Metadata\MetadataManager;

use VpLogger\Log\Logger;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;

/**
 * SiteManager
 */
class SiteManager implements SiteManagerInterface,
                             EventManagerAwareInterface
{
    /**
     * Event manager
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * SiteEvent token
     * @var SiteEventInterface
     */
    protected $siteEvent;

    /**
     * Module manager
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * RouteMatch object
     * @var RouteMatch
     */
    protected $routeMatch;

    /**
     * Name of the route param containing the host name
     * @var string
     */
    protected $routeParamHost;

    /**
     * Module manager factory
     * @var ModuleManagerFactory
     */
    protected $moduleManagerFactory;

    /**
     * List of names of core modules (loaded for all sites)
     * @var array
     */
    protected $coreModules    = array();

    /**
     * Module Storage Manager
     * @var ModuleStorageManager
     */
    protected $moduleStorageManager;

    /**
     * Site API
     * @var SiteApi
     */
    protected $siteApi;

    /**
     * Application's service manager
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var ModuleResourceManager
     */
    protected $moduleResourceManager;

    /**
     * Metadata Manager
     * @var MetadataManager
     */
    protected $metadataManager;

    /**
     * Constructor
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param Event\SiteEventInterface $siteEvent
     * @param string $routeParamHost Name of route parameter containing the host name
     * @param \Vivo\Module\ModuleManagerFactory $moduleManagerFactory
     * @param array $coreModules
     * @param \Vivo\Module\StorageManager\StorageManager $moduleStorageManager
     * @param \Vivo\CMS\Api\Site $siteApi
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     * @param \Vivo\Module\ResourceManager\ResourceManager $moduleResourceManager
     * @param \Zend\Mvc\Router\RouteMatch $routeMatch
     * @param \Vivo\Metadata\MetadataManager $metadataManager
     */
    public function __construct(EventManagerInterface $events,
                                SiteEventInterface $siteEvent,
                                $routeParamHost,
                                ModuleManagerFactory $moduleManagerFactory,
                                array $coreModules,
                                ModuleStorageManager $moduleStorageManager,
                                SiteApi $siteApi,
                                ServiceManager $serviceManager,
                                ModuleResourceManager $moduleResourceManager,
                                RouteMatch $routeMatch = null,
                                MetadataManager $metadataManager)
    {
        $this->setEventManager($events);
        $this->siteEvent            = $siteEvent;
        $this->routeParamHost       = $routeParamHost;
        $this->moduleManagerFactory = $moduleManagerFactory;
        $this->coreModules          = $coreModules;
        $this->moduleStorageManager = $moduleStorageManager;
        $this->siteApi              = $siteApi;
        $this->serviceManager       = $serviceManager;
        $this->moduleResourceManager    = $moduleResourceManager;
        $this->setRouteMatch($routeMatch);
        $this->metadataManager      = $metadataManager;
    }

    /**
     * Bootstraps the SiteManager
     */
    public function bootstrap()
    {
        $this->siteEvent->setTarget($this);
        $this->siteEvent->setRouteMatch($this->routeMatch);

        //Attach Site model load listener
        $cmsEvent   = $this->serviceManager->get('cms_event');
        $listener   = new SiteModelLoadListener($this->routeParamHost, $this->siteApi, $cmsEvent);
        $listener->attach($this->events);
        //Attach Site config listener
        $listener   = new SiteConfigListener($this->siteApi);
        $listener->attach($this->events);
        //Attach Collect site modules listener
        $listener   = new CollectSiteModulesListener();
        $listener->attach($this->events);
        //Attach Load modules listener
        $listener   = new LoadModulesListener($this->moduleManagerFactory,
                                              $this->serviceManager,
                                              $this->moduleStorageManager,
                                              $this->metadataManager);
        $listener->attach($this->events);
        //Attach InjectModuleManagerListener
        $listener   = new InjectModuleManagerListener($this->moduleResourceManager);
        $listener->attach($this->events);
        //Attach InjectSecurityManagerListener
        $listener   = new InjectSecurityManagerListener($this->serviceManager);
        $listener->attach($this->events);

        $routeName  = $this->routeMatch->getMatchedRouteName();
        if ((strpos($routeName, 'backend/') === 0) && ($routeName != 'backend/cms')) {
            //Attach Backend config listener
            $listener   = new BackendConfigListener();
            $listener->attach($this->events);
            //Attach Collect backend modules listener
            $listener   = new CollectBackendModulesListener();
            $listener->attach($this->events);
            //Attach Reconfigure for backend listener
            $listener   = new ReconfigureForBackendListener($this->serviceManager);
            $listener->attach($this->events);
        }

        //Attach generic load modules post listener
        $listener   = new LoadModulesPostListener($this->serviceManager);
        $listener->attach($this->events);
    }

    /**
     * Prepares the site
     */
    public function prepareSite()
    {
        //Prime the siteEvent with the global modules
        $this->siteEvent->setModules($this->coreModules);
        //Trigger events
        //Load the Site model
        $this->siteEvent->stopPropagation(false);
        $this->events->trigger(SiteEventInterface::EVENT_SITE_MODEL_LOAD, $this->siteEvent);
        //Get Site config
        $this->siteEvent->stopPropagation(false);
        $this->events->trigger(SiteEventInterface::EVENT_CONFIG, $this->siteEvent);
        //Get module names loaded for the site
        $this->siteEvent->stopPropagation(false);
        $this->events->trigger(SiteEventInterface::EVENT_COLLECT_MODULES, $this->siteEvent);
        //Load site modules
        $this->siteEvent->stopPropagation(false);
        $this->events->trigger(SiteEventInterface::EVENT_LOAD_MODULES, $this->siteEvent);
        //Perform post module loading tasks
        $this->siteEvent->stopPropagation(false);
        $this->events->trigger(SiteEventInterface::EVENT_LOAD_MODULES_POST, $this->siteEvent);

        //Performance log
        if ($this->siteEvent->getHost()) {
            $siteHost   = $this->siteEvent->getHost();
        } else {
            $siteHost   = '<site host unknown>';
        }
        $this->events->trigger('log', $this,
                array ('message'    => "Site at host '" . $siteHost . "' prepared",
                       'priority'   => Logger::PERF_BASE));

    }

    /**
     * Inject an EventManager instance
     * @param  EventManagerInterface $eventManager
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events   = $eventManager;
    }

    /**
     * Retrieve the event manager
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * Sets the RouteMatch object
     * @param \Zend\Mvc\Router\RouteMatch|null $routeMatch
     */
    public function setRouteMatch(RouteMatch $routeMatch = null)
    {
        $this->routeMatch = $routeMatch;
        if ($routeMatch && $routeMatch->getParam('path') && $this->getEventManager()) {
            $events = $this->getEventManager();
            $path   = $routeMatch->getParam('path');
            //Performance log
            $events->trigger('log', $this,
                array ('message'    => sprintf("Routematch set (path = '%s')", $path),
                    'priority'   => Logger::PERF_BASE));
        }
    }
}