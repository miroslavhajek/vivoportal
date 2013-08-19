<?php
namespace Vivo\Backend\UI;

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
        $cacheApi   = $serviceLocator->get('Vivo\CMS\Api\Cache');
        $module = new Cache($cacheApi);
        return $module;
    }
}
