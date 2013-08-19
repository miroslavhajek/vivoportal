<?php
namespace Vivo\Controller\CLI;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for CLI\Repository controller.
 */
class IndexerControllerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm             = $serviceLocator->getServiceLocator();
        $indexer        = $sm->get('indexer');
        $indexerApi     = $sm->get('Vivo\CMS\Api\Indexer');
        $siteEvent      = $sm->get('site_event');
        $indexerEvents  = $sm->get('indexer_events');
        $controller     = new IndexerController($indexer, $indexerApi, $siteEvent, $indexerEvents);
        return $controller;
    }
}
