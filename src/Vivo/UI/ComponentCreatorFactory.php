<?php
namespace Vivo\UI;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ComponentCreatorFactory
 */
class ComponentCreatorFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $diProxy    = $serviceLocator->get('di_proxy');
        $service    = new ComponentCreator($serviceLocator, $diProxy);
        return $service;
    }
}
