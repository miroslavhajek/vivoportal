<?php
namespace Vivo\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * IndexerHelperFactory
 * Instantiates the indexer helper
 */
class IndexerHelperFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\UnsupportedIndexerAdapterException
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $indexerFieldHelper = $serviceLocator->get('indexer_field_helper');
        $fileApi            = $serviceLocator->get('Vivo\CMS\Api\Content\File');
        
        $indexerHelper      = new \Vivo\CMS\Indexer\IndexerHelper($indexerFieldHelper, $fileApi);
        
        return $indexerHelper;
    }
}
