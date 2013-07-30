<?php
namespace Vivo\Backend\UI\Explorer;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * DeleteFactory
 */
class DeleteFactory implements FactoryInterface
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
        $alert              = $serviceLocator->get('Vivo\UI\Alert');
        $urlHelper          = $serviceLocator->get('Vivo\Util\UrlHelper');
        $delete             = new Delete($cmsApi, $documentApi, $alert, $urlHelper);
        return $delete;
    }
}
