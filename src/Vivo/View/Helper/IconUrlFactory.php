<?php
namespace Vivo\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * IconUrlFactory
 */
class IconUrlFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm              = $serviceLocator->getServiceLocator();
        $metadataManager = $sm->get('metadata_manager');
        $documentApi     = $sm->get('Vivo\CMS\Api\Document');
        $mime            = $sm->get('mime');
        $helper          = new IconUrl($metadataManager, $documentApi, $mime);
        return $helper;
    }
}
