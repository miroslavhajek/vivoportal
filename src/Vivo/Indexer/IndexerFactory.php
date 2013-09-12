<?php
namespace Vivo\Indexer;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * IndexerFactory
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
        $adapter        = $serviceLocator->get('indexer_adapter');
        $config         = $serviceLocator->get('config');
        $indexer        = new Indexer($adapter, $config['indexer']);
        return $indexer;
    }
}
