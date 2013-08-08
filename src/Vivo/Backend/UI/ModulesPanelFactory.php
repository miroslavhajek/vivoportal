<?php
namespace Vivo\Backend\UI;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ModulesPanelFactory
 */
class ModulesPanelFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $moduleResolver = $serviceLocator->get('Vivo\Backend\ModuleResolver');
        $modulesPanel   = new ModulesPanel($moduleResolver);
        return $modulesPanel;
    }
}
