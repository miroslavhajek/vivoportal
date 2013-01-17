<?php
namespace Vivo\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * IndexerFieldHelperFactory
 * Instantiates the indexer field helper
 */
class IndexerFieldHelperFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\UnsupportedIndexerAdapterException
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config             = $serviceLocator->get('config');
        if (isset($config['vivo']['indexer']['default_indexing_options'])) {
            $defaultIndexingOptions = $config['vivo']['indexer']['default_indexing_options'];
        } else {
            $defaultIndexingOptions = array();
        }
        if (isset($config['vivo']['indexer']['presets'])) {
            $presets    = $config['vivo']['indexer']['presets'];
        } else {
            $presets    = array();
        }
        $metadataManager    = $serviceLocator->get('metadata_manager');
        $pathBuilder        = $serviceLocator->get('path_builder');
        $fieldHelper        = new \Vivo\Indexer\FieldHelper($metadataManager,
                                                            $pathBuilder,
                                                            $defaultIndexingOptions,
                                                            $presets);
        return $fieldHelper;
    }
}
