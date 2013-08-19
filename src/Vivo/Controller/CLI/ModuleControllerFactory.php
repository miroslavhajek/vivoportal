<?php
namespace Vivo\Controller\CLI;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for CLI\Module controller.
 */
class ModuleControllerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm                     = $serviceLocator->getServiceLocator();
        $moduleStorageManager   = $sm->get('module_storage_manager');
        $remoteModule           = $sm->get('remote_module');
        $repository             = $sm->get('repository');
        $moduleApi              = $sm->get('Vivo\CMS\Api\Module');
        $controller             = new ModuleController($moduleStorageManager, $remoteModule, $repository, $moduleApi);
        return $controller;
    }
}
