<?php
namespace Vivo\Transliterator;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Factory for RemoveAccentsFactory - transliterator used to remove various accents
 */
class RemoveAccentsFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return Transliterator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config     = $serviceLocator->get('config');
        if (isset($config['cache']['translit_remove_accents'])) {
            $cacheManager   = $serviceLocator->get('Vivo\cache_manager');
            $cache          = $cacheManager->get($config['cache']['translit_remove_accents']);
        } else {
            $cache      = null;
        }
        if (isset($config['transliterator']['remove_accents']['options'])) {
            $options    = $config['transliterator']['remove_accents']['options'];
        } else {
            $options    = array();
        }
        $translit   = new Transliterator($cache, $options);
        return $translit;
    }
}
