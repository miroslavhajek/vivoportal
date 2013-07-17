<?php
namespace Vivo\View\Resolver;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * CmsFactory
 */
class TemplateResolverFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Vivo\View\Resolver\TemplateResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cmsConfig = $serviceLocator->get('cms_config');
        $config = $serviceLocator->get('config');
        $configOptions = $config['options'];
        return new TemplateResolver(
                $serviceLocator->get('module_resource_manager'),
                new \Vivo\Util\Path\PathParser(),
                $cmsConfig['templates'], $configOptions);
    }
}
