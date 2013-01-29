<?php
namespace Vivo\Service\Controller\CLI;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for CLI\Setup controller
 */
class CLISetupControllerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm             = $serviceLocator->getServiceLocator();
        /** @var $dbProviderCore \Vivo\Service\DbProviderInterface */
        $dbProviderCore = $sm->get('db_provider_core');
        $zdba           = $dbProviderCore->getZendDbAdapter();
        $controller     = new \Vivo\Controller\CLI\SetupController($zdba);
        return $controller;
    }
}
