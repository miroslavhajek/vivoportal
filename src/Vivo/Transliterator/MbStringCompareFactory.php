<?php
namespace Vivo\Transliterator;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Factory for MbStringCompareFactory - transliterator used for unicode string comparison
 */
class MbStringCompareFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return Transliterator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config     = $serviceLocator->get('config');
        if (isset($config['transliterator']['mb_string_compare']['cache'])) {
            $cacheManager   = $serviceLocator->get('Vivo\cache_manager');
            $cache          = $cacheManager->get($config['transliterator']['mb_string_compare']['cache']);
        } else {
            $cache      = null;
        }
        if (isset($config['transliterator']['mb_string_compare']['options'])) {
            $options    = $config['transliterator']['mb_string_compare']['options'];
        } else {
            $options    = array();
        }
        $translit   = new Transliterator($cache, $options);
        return $translit;
    }
}
