<?php
namespace Vivo;

use Vivo\View\Helper as ViewHelper;
use VpLogger\Log\Logger;

use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;

class Module implements ConsoleBannerProviderInterface, ConsoleUsageProviderInterface
{
    /**
     * Module bootstrap method.
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        //Get basic objects
        /** @var $sm ServiceManager */
        $sm             = $e->getApplication()->getServiceManager();
        $eventManager   = $e->getApplication()->getEventManager();
//        $config         = $sm->get('config');
        //Register custom navigation view helpers
        $navHelperRegistrar   = new \Vivo\Service\NavigationHelperRegistrar();
        $navHelperRegistrar->registerNavigationHelpers($sm);
        //Performance log
        $eventManager->trigger('log', $this,
            array ('message'    => 'Vivo Portal Module bootstrapped',
                   'priority'   => Logger::PERF_BASE));
    }

    public function getConfig()
    {
        $config = include __DIR__ . '/config/module.config.php'; //main vivo config
        $config['cms'] = include __DIR__ . '/config/cms.config.php'; //CMS config
        return $config;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConsoleBanner(Console $console)
    {
        return "Vivo 2 CLI\n";
    }

    public function getConsoleUsage(Console $console)
    {
        return array('Available commands:',
                array ('indexer', 'Perform operations on indexer..'),
                array ('info','Show information about CMS instance.'),
                array ('module', 'Manage modules.'),
                array ('cms', 'CMS functions.'),
                array ('setup', 'System setup'),
                array ('util', 'Utilities'),
        );
    }
}
