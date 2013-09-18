<?php
namespace Vivo\Controller\CLI;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for CLI\Setup controller
 */
class SetupControllerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm                     = $serviceLocator->getServiceLocator();
        /** @var $dbProviderCore \Vivo\Db\DbProviderInterface */
        $dbProviderCore         = $sm->get('db_provider_core');
        $zdba                   = $dbProviderCore->getZendDbAdapter();
        $dbTableNameProvider    = $sm->get('db_table_name_provider');
        $controller             = new SetupController($zdba, $dbTableNameProvider);
        return $controller;
    }
}
