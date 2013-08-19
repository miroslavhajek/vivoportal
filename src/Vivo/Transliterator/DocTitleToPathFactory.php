<?php
namespace Vivo\Transliterator;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * DocTitleToPathFactory
 */
class DocTitleToPathFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config     = $serviceLocator->get('config');
        if (isset($config['cache']['translit_doc_title_to_path'])) {
            $cacheManager   = $serviceLocator->get('Vivo\cache_manager');
            $cache          = $cacheManager->get($config['cache']['translit_doc_title_to_path']);
        } else {
            $cache      = null;
        }
        if (isset($config['transliterator']['doc_title_to_path']['options'])) {
            $options    = $config['transliterator']['doc_title_to_path']['options'];
        } else {
            $options    = array();
        }
        $translit   = new Transliterator($cache, $options);
        return $translit;
    }
}
