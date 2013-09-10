<?php
namespace Vivo\CMS\UI\Content;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class SearchFactory implements FactoryInterface
{
    /**
     * Create UI Search object.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return \Zend\Stdlib\Message
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {        
        $cms         = $serviceLocator->get('Vivo\CMS\Api\CMS');   
        $indexer     = $serviceLocator->get('indexer');
        $documentApi = $serviceLocator->get('Vivo\CMS\Api\Document');
        $siteEvent   = $serviceLocator->get('site_event');
        $paginator   = $serviceLocator->create('Vivo\UI\Paginator');

        $service    = new Search($cms, $indexer, $documentApi, $siteEvent, $paginator);       

        return $service;
    }
}
