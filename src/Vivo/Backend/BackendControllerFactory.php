<?php
namespace Vivo\Backend;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Factory for Backend controller
 */
class BackendControllerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $bc = new BackendController($sm->get('security_manager'));
        $bc->setModuleResolver($sm->get('Vivo\Backend\ModuleResolver'));
        $bc->setComponentTreeController($sm->get('component_tree_controller'));
        $bc->setSiteEvent($sm->get('site_event'));
        $bc->setRedirector($sm->get('redirector'));
        $bc->setUrlHelper($sm->get('Vivo\Util\UrlHelper'));
        $bc->setSiteApi($sm->get('Vivo\CMS\Api\Site'));
        $bc->setServiceManager($sm);
        return $bc;
    }
}
