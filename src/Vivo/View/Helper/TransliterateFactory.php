<?php
namespace Vivo\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * TransliterateFactory
 */
class TransliterateFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm     = $serviceLocator->getServiceLocator();
        $transliterators    = array(
            'path'              => $sm->get('Vivo\Transliterator\Path'),
            'url'               => $sm->get('Vivo\Transliterator\Url'),
            'title_to_path'     => $sm->get('Vivo\Transliterator\DocTitleToPath'),
        );
        $options            = array(
            'default_transliterator'    => 'title_to_path',
        );
        $helper = new Transliterate($transliterators, $options);
        return $helper;
    }
}
