<?php
namespace Vivo\CMS\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FetchDocumentByUrlListenerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Vivo\CMS\FetchErrorDocumentListener
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        return new FetchDocumentByUrlListener($sm->get('Vivo\CMS\Api\Indexer'));
    }
}
