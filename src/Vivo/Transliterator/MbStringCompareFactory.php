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
        if (isset($config['transliterator']['doc_title_to_path']['options'])) {
            $options    = $config['transliterator']['mb_string_compare']['options'];
        } else {
            $options    = array();
        }
        $translit   = new Transliterator($options);
        return $translit;
    }
}
