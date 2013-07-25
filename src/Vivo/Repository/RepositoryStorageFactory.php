<?php
namespace Vivo\Repository;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * RepositoryStorageFactory
 */
class RepositoryStorageFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\ConfigException
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        if (!isset($config['repository']['storage'])) {
            throw new Exception\ConfigException(sprintf("%s: Repository storage configuration missing", __METHOD__));
        }
        $storageConfig  = $config['repository']['storage'];
        $storageConfig['options']['path_builder']   = $serviceLocator->get('path_builder');
        $storageFactory         = $serviceLocator->get('storage_factory');
        /* @var $storageFactory \Vivo\Storage\Factory */
        $storage                = $storageFactory->create($storageConfig);
        return $storage;
    }
}
