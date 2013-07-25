<?php
namespace Vivo\CMS\UI\Content\Editor;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FileboardFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $docApi = $serviceLocator->get('Vivo\CMS\Api\Document');
        $fileboardApi = $serviceLocator->get('Vivo\CMS\Api\Content\Fileboard');

        return new Fileboard($docApi, $fileboardApi);
    }

}
