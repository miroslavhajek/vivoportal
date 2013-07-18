<?php
namespace Vivo\CMS\Util;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * ResourceUrlHelper Factory
 */
class ResourceUrlHelperFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return ResourceUrlHelper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm                     = $serviceLocator;
        /** @var $application \Zend\Mvc\Application */
        $application            = $sm->get('application');
        $mvcEvent               = $application->getMvcEvent();
        $routeMatch             = $mvcEvent->getRouteMatch();
        $routeName              = $routeMatch->getMatchedRouteName();
        $cmsApi                 = $sm->get('Vivo\CMS\Api\CMS');
        $moduleResourceManager  = $sm->get('module_resource_manager');
        $urlHelper              = $sm->get('Vivo\Util\UrlHelper');
        $resourceHelperOptions  = array(
            'vivo_resource_path'    => realpath(__DIR__ . '/../../../../resource/'),
        );
        $helper                 = new ResourceUrlHelper($cmsApi,
                                                        $moduleResourceManager,
                                                        $urlHelper,
                                                        $routeName,
                                                        $resourceHelperOptions);
        return $helper;
    }
}
