<?php
namespace Vivo\Serializer\Adapter;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * EntityFactory
 */
class EntityFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\ConfigException
     * @return \Vivo\Serializer\Adapter\Entity
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $textService     = $serviceLocator->get('Vivo\text');
        $metadataManager = $serviceLocator->get('metadata_manager');

        return new Entity($textService, $metadataManager);
    }
}
