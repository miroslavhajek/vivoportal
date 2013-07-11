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
        $document   = $serviceLocator->get('Vivo\CMS\Api\Document');
        $indexer    = $serviceLocator->get('indexer');
        $siteEvent  = $serviceLocator->get('site_event');
        $site       = $siteEvent->getSite();

        $finder = new Finder($document, $indexer, $site);
        $finder->setAlert($serviceLocator->get('Vivo\UI\Alert'));

        return $finder;
    }
}
