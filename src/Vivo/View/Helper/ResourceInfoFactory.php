<?php
namespace Vivo\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResourceInfoFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();

        return new ResourceInfo($sm->get('Vivo\CMS\Api\CMS'), $sm->get('mime'));
    }
}
