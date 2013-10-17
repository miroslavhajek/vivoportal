<?php
namespace Vivo\Cache;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * CacheManager
 * Plugin manager for cache instances in Vivo Portal
 */
class CacheManager extends AbstractPluginManager
{
    /**
     * Validate the plugin
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if (!$plugin instanceof \Zend\Cache\Storage\StorageInterface) {
            throw new Exception\RuntimeException(
                sprintf("%s: The CacheManager is supposed to create only %s instances; Object of type '%s' created",
                    __METHOD__, 'Zend\Cache\Storage\StorageInterface', get_class($plugin)));
        }
    }
}
