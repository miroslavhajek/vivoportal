<?php
namespace Vivo\Controller\CLI;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for CLI\Cms controller.
 */
class CmsControllerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm             = $serviceLocator->getServiceLocator();
        $cmsApi         = $sm->get('Vivo\CMS\Api\CMS');
        $siteApi        = $sm->get('Vivo\CMS\Api\Site');
        $siteEvent      = $sm->get('site_event');
        $repository     = $sm->get('repository');
        $uuidGenerator  = $sm->get('uuid_generator');
        $indexerApi     = $sm->get('Vivo\CMS\Api\Indexer');
        $controller     = new CmsController($cmsApi, $siteApi, $siteEvent, $repository, $uuidGenerator, $indexerApi);
        return $controller;
    }
}
