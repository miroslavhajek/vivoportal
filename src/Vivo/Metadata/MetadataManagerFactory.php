<?php
namespace Vivo\Metadata;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MetadataManagerFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @return \Vivo\Metadata\Metadata\MetadataManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $resource = $serviceLocator->get('module_resource_manager');
        $resolver = $serviceLocator->get('module_name_resolver');
        $config = $serviceLocator->get('config');
        $config = $config['metadata_manager'];

        $manager = new MetadataManager($serviceLocator, $resource, $resolver, $config);

        return $manager;
    }
}
