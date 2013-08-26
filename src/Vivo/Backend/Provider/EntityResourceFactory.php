<?php
namespace Vivo\Backend\Provider;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * EntityResourceFactory
 */
class EntityResourceFactory implements FactoryInterface
{
    /**
     * Create service
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Vivo\Backend\Provider\EntityResource
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EntityResource($serviceLocator->get('Vivo\CMS\Api\CMS'));
    }
}
