<?php
namespace Vivo\Cache;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Config;

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
        /** @var $status \Vivo\Service\Status */
        $status                 = $serviceLocator->get('Vivo\status');
        $serveNullCache         = $status->isConsoleRequest() || $status->isBackend();
        $cacheAbstractFactory   = new CacheAbstractFactory($serveNullCache, $options);
        $configAy   = array();
        if (isset($config['cache']) && is_array($config['cache'])) {
            foreach ($config['cache'] as $cacheSymName => $cacheName) {
                if (is_null($cacheName) || $cacheName == '') {
                    continue;
                }
                $configAy['aliases'][$cacheSymName] = $cacheName;
            }
        }
        $pluginManagerConfig    = new Config($configAy);
        $service                = new CacheManager($pluginManagerConfig);
        $service->addAbstractFactory($cacheAbstractFactory);
        return $service;
    }
}
