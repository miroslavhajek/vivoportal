<?php
namespace Vivo\Backend\UI\Explorer;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Editor factory.
 */
class EditorFactory implements FactoryInterface
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
        $provider           = $sm->get('Vivo\CMS\AvailableContentsProvider');
        $alert              = $sm->get('Vivo\UI\Alert');
        $urlHelper          = $sm->get('Vivo\Util\UrlHelper');
        $newFormFactory     = $sm->get('Vivo\new_form_factory');

        $editor = new Editor($sm, $metadataManager, $lookupDataManager,
                             $documentApi, $provider, $urlHelper, $newFormFactory);
        $editor->setTabContainer($sm->create('Vivo\UI\TabContainer'));
        $editor->setResourceEditor($sm->create('Vivo\Backend\UI\Explorer\Editor\Resource'));
        $editor->setAlert($alert);

        return $editor;
    }
}
