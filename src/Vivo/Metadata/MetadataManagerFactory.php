<?php
namespace Vivo\Metadata;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MetadataManagerFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @return MetadataManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $resource   = $serviceLocator->get('module_resource_manager');
        $resolver   = $serviceLocator->get('module_name_resolver');
        $config     = $serviceLocator->get('config');
        if (isset($config['metadata_manager']) && is_array($config['metadata_manager'])) {
            $mmOptions  = $config['metadata_manager'];
        } else {
            $mmOptions  = array();
        }
        $manager = new MetadataManager($serviceLocator, $resource, $resolver, $mmOptions);
        return $manager;
    }
}
