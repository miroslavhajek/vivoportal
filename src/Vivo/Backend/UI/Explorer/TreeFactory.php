<?php
namespace Vivo\Backend\UI\Explorer;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * TreeFactory
 */
class TreeFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cmsConfig      = $serviceLocator->get('cms_config');
        if (isset($cmsConfig['backend']['tree']['options'])) {
            $options    = $cmsConfig['backend']['tree']['options'];
        } else {
            $options    = array();
        }
        $cmsApi         = $serviceLocator->get('Vivo\CMS\Api\CMS');
        $documentApi    = $serviceLocator->get('Vivo\CMS\Api\Document');
        $tree           = new Tree($cmsApi, $documentApi, $options);
        $view           = $serviceLocator->get('view_model');
        $tree->setView($view);
        return $tree;
    }
}
