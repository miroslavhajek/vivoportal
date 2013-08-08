<?php
namespace Vivo\CMS;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * FetchDocumentListenerFactory
 */
class FetchDocumentListenerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cmsApi     = $serviceLocator->get('Vivo\CMS\Api\CMS');
        $listener   = new FetchDocumentListener($cmsApi);
        return $listener;
    }
}
