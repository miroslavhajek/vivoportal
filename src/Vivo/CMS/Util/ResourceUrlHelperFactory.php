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
        /** @var $application \Zend\Mvc\Application */
        $application            = $serviceLocator->get('application');
        $routeName              = $application->getMvcEvent()->getRouteMatch()->getMatchedRouteName();
        $cmsApi                 = $serviceLocator->get('Vivo\CMS\Api\CMS');
        $moduleResourceManager  = $serviceLocator->get('module_resource_manager');
        $urlHelper              = $serviceLocator->get('Vivo\Util\UrlHelper');
        //TODO - move to module config
        $resourceHelperOptions  = array(
            'vivo_resource_path'    => realpath(__DIR__ . '/../../../../resource'),
        );
        $helper                 = new ResourceUrlHelper($cmsApi,
                                                        $moduleResourceManager,
                                                        $urlHelper,
                                                        $routeName,
                                                        $resourceHelperOptions);
        return $helper;
    }
}
