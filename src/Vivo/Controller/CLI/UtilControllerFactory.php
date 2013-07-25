<?php
namespace Vivo\Controller\CLI;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * UtilControllerFactory
 */
class UtilControllerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm             = $serviceLocator->getServiceLocator();
        $utilApi        = $sm->get('Vivo\CMS\Api\Util');
        $cmsEvent       = $sm->get('cms_event');
        $controller     = new UtilController($sm, $utilApi, $cmsEvent);
        return $controller;
    }
}
