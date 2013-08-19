<?php
namespace Vivo\Transliterator;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * UrlFactory
 */
class UrlFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config     = $serviceLocator->get('config');
        if (isset($config['cache']['translit_url'])) {
            $cacheManager   = $serviceLocator->get('Vivo\cache_manager');
            $cache          = $cacheManager->get($config['cache']['translit_url']);
        } else {
            $cache      = null;
        }
        if (isset($config['transliterator']['url']['options'])) {
            $options    = $config['transliterator']['url']['options'];
        } else {
            $options    = array();
        }
        $translit   = new Transliterator($cache, $options);
        return $translit;
    }
}
