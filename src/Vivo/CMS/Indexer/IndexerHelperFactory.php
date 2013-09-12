<?php
namespace Vivo\CMS\Indexer;

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
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Vivo\CMS\Indexer\IndexerHelper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $indexerFieldHelper = $serviceLocator->get('indexer_field_helper');
        $fileApi            = $serviceLocator->get('Vivo\CMS\Api\Content\File');
        $config             = $serviceLocator->get('config');
        if (isset($config['indexer_helper']) && is_array($config['indexer_helper'])) {
            $options            = $config['indexer_helper'];
        } else {
            $options            = array();
        }
        $indexerHelper      = new IndexerHelper($indexerFieldHelper, $fileApi, $options);
        return $indexerHelper;
    }
}
