<?php
namespace Vivo\CMS\Api;

use Vivo\CMS\Api\Cache as CacheApi;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * CacheFactory
 */
class CacheFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cacheManager   = $serviceLocator->get('Vivo\cache_manager');
        $config         = $serviceLocator->get('config');
        if (isset($config['cache_manager']) && is_array($config['cache_manager'])) {
            $cacheManagerConfig = $config['cache_manager'];
        } else {
            $cacheManagerConfig = array();
        }
        if (isset($config['cache']) && is_array($config['cache'])) {
            $usedCaches = $config['cache'];
        } else {
            $usedCaches     = array();
        }
        $api    = new CacheApi($cacheManager, $cacheManagerConfig, $usedCaches);
        return $api;
    }
}
