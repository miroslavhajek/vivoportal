<?php
namespace Vivo\CMS\Util;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * IconUrlHelperFactory
 */
class IconUrlHelperFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $metadataManager = $serviceLocator->get('metadata_manager');
        $documentApi     = $serviceLocator->get('Vivo\CMS\Api\Document');
        $mime            = $serviceLocator->get('mime');
        $resourceUrlHelper = $serviceLocator->get('Vivo\resource_url_helper');
        $helper          = new IconUrlHelper($metadataManager, $documentApi, $mime, $resourceUrlHelper);
        return $helper;
    }
}
