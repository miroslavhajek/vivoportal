<?php
namespace Vivo\CMS\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FetchDocumentListenerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Vivo\CMS\FetchErrorDocumentListener
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        return new FetchDocumentListener($sm->get('Vivo\CMS\Api\CMS'));
    }
}
