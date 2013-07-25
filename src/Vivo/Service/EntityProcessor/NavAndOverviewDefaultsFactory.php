<?php
namespace Vivo\Service\EntityProcessor;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * NavAndOverviewDefaultsFactory
 */
class NavAndOverviewDefaultsFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cmsApi     = $serviceLocator->get('Vivo\CMS\Api\CMS');
        $processor  = new NavAndOverviewDefaults($cmsApi);
        return $processor;
    }
}
