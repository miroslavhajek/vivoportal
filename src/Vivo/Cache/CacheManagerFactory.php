<?php
namespace Vivo\Cache;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Console\Request as ConsoleRequest;


/**
 * CacheManagerFactory
 */
class CacheManagerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //Add Vivo Null cache adapter to cache adapter plugin manager
        $storageAdapterPluginManager    = \Zend\Cache\StorageFactory::getAdapterPluginManager();
        $storageAdapterPluginManager->setInvokableClass('Vivo\null', 'Vivo\Cache\Adapter\Null');
        $config                 = $serviceLocator->get('config');
        if (isset($config['cache_manager'])) {
            $options    = $config['cache_manager'];
        } else {
            $options    = array();
        }
        $request        = $serviceLocator->get('request');
        if ($request instanceof ConsoleRequest) {
            $isConsoleRequest   = true;
        } else {
            $isConsoleRequest   = false;
        }
        $cacheAbstractFactory   = new CacheAbstractFactory($isConsoleRequest, $options);
        $service                = new CacheManager();
        $service->addAbstractFactory($cacheAbstractFactory);
        return $service;
    }
}
