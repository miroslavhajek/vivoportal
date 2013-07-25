<?php
namespace Vivo\CMS\Api;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * IndexerFactory
 * Indexer API Factory
 */
class IndexerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $indexer            = $serviceLocator->get('indexer');
        $indexerHelper      = $serviceLocator->get('indexer_helper');
        $queryParser        = $serviceLocator->get('indexer_query_parser');
        $queryBuilder       = $serviceLocator->get('indexer_query_builder');
        $repository         = $serviceLocator->get('repository');
        $documentApi        = $serviceLocator->get('Vivo\CMS\Api\Document');
        $pathBuilder        = $serviceLocator->get('path_builder');
        $repositoryEvents   = $serviceLocator->get('repository_events');
        $indexerEvents      = $serviceLocator->get('indexer_events');
        $watcher            = $serviceLocator->get('Vivo\watcher');
        $service            = new Indexer($indexer, $indexerHelper, $queryParser, $queryBuilder, $repository,
                                          $documentApi, $pathBuilder, $indexerEvents, $repositoryEvents, $watcher);
        return $service;
    }
}
