<?php
namespace Vivo\Db;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * DbProviderFactoryFactory
 */
class DbProviderFactoryFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dbServiceManager   = $serviceLocator->get('db_service_manager');
        $dbProviderFactory  = new DbProviderFactory($dbServiceManager);
        return $dbProviderFactory;
    }
}
