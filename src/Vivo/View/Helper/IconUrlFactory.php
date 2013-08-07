<?php
namespace Vivo\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * IconUrlFactory
 */
class IconUrlFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm             = $serviceLocator->getServiceLocator();
        $iconUrlHelper  = $sm->get('Vivo\icon_url_helper');

        return new IconUrl($iconUrlHelper);
    }
}
