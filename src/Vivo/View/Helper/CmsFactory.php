<?php
namespace Vivo\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Class CmsFactory
 * @package Vivo\CMS\Api
 */
class CmsFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm             = $serviceLocator->getServiceLocator();
        $cmsEvent       = $sm->get('cms_event');
        $documentApi    = $sm->get('Vivo\CMS\Api\Document');
        $helper         = new Cms($cmsEvent, $documentApi);
        return $helper;
    }
}
