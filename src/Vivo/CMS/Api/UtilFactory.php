<?php
namespace Vivo\CMS\Api;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * UtilFactory
 */
class UtilFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cmsApi = $serviceLocator->get('Vivo\CMS\Api\CMS');
        $api    = new Util($cmsApi);
        return $api;
    }
}
