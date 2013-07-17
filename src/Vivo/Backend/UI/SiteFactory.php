<?php
namespace Vivo\Backend\UI;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Editor factory.
 */
class SiteFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm                 = $serviceLocator->get('service_manager');
        $metadataManager    = $sm->get('metadata_manager');
        $lookupDataManager  = $sm->get('lookup_data_manager');
        $documentApi        = $sm->get('Vivo\CMS\Api\Document');
        $alert              = $sm->get('Vivo\UI\Alert');
        $urlHelper          = $sm->get('Vivo\Util\UrlHelper');
        $cmsEvent           = $sm->get('cms_event');

        $site = new Site($sm, $metadataManager, $lookupDataManager, $cmsEvent, $urlHelper, $documentApi);
        $site->setAlert($alert);

        return $site;
    }
}
