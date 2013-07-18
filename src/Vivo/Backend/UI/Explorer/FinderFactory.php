<?php
namespace Vivo\Backend\UI\Explorer;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Editor factory.
 */
class FinderFactory implements FactoryInterface
{
    /**
     * Create Finder
     * @param ServiceLocatorInterface $serviceLocator
     * @return Finder
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cms        = $serviceLocator->get('Vivo\CMS\Api\CMS');
        $document   = $serviceLocator->get('Vivo\CMS\Api\Document');
        $indexer    = $serviceLocator->get('indexer');
        $urlHelper  = $serviceLocator->get('Vivo\document_url_helper');
        $siteEvent  = $serviceLocator->get('site_event');
        $site       = $siteEvent->getSite();

        $finder = new Finder($cms, $document, $indexer, $urlHelper, $site);
        $finder->setAlert($serviceLocator->get('Vivo\UI\Alert'));

        return $finder;
    }
}
