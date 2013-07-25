<?php
namespace Vivo\CMS;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for FrontController
 */
class FetchErrorDocumentListenerFactory implements FactoryInterface
{
    /**
     * Creates CMS front controller.
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Vivo\CMS\FetchErrorDocumentListener
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        $config = $sm->get('cms_config');

        $listener = new FetchErrorDocumentListener($sm->get('Vivo\CMS\Api\Document'), $config['error_documents']);

        return $listener;
    }
}
