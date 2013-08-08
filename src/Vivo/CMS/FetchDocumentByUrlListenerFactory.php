<?php
namespace Vivo\CMS;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * FetchDocumentByUrlListenerFactory
 */
class FetchDocumentByUrlListenerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $indexerApi = $serviceLocator->get('Vivo\CMS\Api\Indexer');
        $listener   = new FetchDocumentByUrlListener($indexerApi);
        return $listener;
    }
}
