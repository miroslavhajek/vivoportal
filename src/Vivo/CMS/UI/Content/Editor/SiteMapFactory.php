<?php
namespace Vivo\CMS\UI\Content\Editor;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SiteMapFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $docApi = $serviceLocator->get('Vivo\CMS\Api\Document');

        $service = new SiteMap($docApi);
        return $service;
    }

}
