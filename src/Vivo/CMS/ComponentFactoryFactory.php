<?php
namespace Vivo\CMS;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * ComponentFactoryFactory
 */
class ComponentFactoryFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cmsApi             = $serviceLocator->get('Vivo\CMS\Api\CMS');
        $documentApi        = $serviceLocator->get('Vivo\CMS\Api\Document');
        $site               = $serviceLocator->get('site_event')->getSite();
        $componentCreator   = $serviceLocator->get('Vivo\component_creator');
        $resolver           = new ComponentResolver($serviceLocator->get('cms_config'));
        $cmsConfig          = $serviceLocator->get('cms_config');
        if (isset($cmsConfig['Vivo\CMS\ComponentFactory'])) {
            $options        = $cmsConfig['Vivo\CMS\ComponentFactory'];
        } else {
            $options        = array();
        }
        $cf             = new ComponentFactory($componentCreator, $cmsApi, $documentApi, $site, $resolver, $options);
        return $cf;
    }
}
