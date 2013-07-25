<?php
namespace Vivo\CMS\Api\Content;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class FileboardFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Vivo\CMS\Api\Content\Fileboard
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cms             = $serviceLocator->get('Vivo\CMS\Api\CMS');
        $file            = $serviceLocator->get('Vivo\CMS\Api\Content\File');
        $indexer         = $serviceLocator->get('indexer');
        $pathBuilder     = $serviceLocator->get('path_builder');

        $api             = new Fileboard($cms, $file, $indexer, $pathBuilder);

        return $api;
    }
}
