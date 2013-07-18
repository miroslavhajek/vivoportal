<?php
namespace Vivo\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * ResourceFactory
 */
class ResourceFactory implements  FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        /* @var $resourceUrlHelper \Vivo\CMS\Util\ResourceUrlHelper */
        $resourceUrlHelper = $sm->get('Vivo\resource_url_helper');
        $helper = new Resource($resourceUrlHelper);
        return $helper;
    }
}
