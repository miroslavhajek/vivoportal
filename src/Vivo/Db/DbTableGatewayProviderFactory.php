<?php
namespace Vivo\Db;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * DbTableGatewayProviderFactory
 */
class DbTableGatewayProviderFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $dbTableNameProvider DbTableNameProvider */
        $dbTableNameProvider    = $serviceLocator->get('db_table_name_provider');
        $moduleDbProvider       = $serviceLocator->get('Vivo\module_db_provider');
        $dbProviderCore         = $serviceLocator->get('db_provider_core');
        $coreZdba               = $dbProviderCore->getZendDbAdapter();
        $service                = new DbTableGatewayProvider($moduleDbProvider, $coreZdba, $dbTableNameProvider);
        return $service;
    }
}
