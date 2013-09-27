<?php
namespace Vivo\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * CurrentSiteFactory
 */
class CurrentSiteFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $siteEvent \Vivo\SiteManager\Event\SiteEvent */
        $siteEvent  = $serviceLocator->get('Vivo\site_event');
        $site       = $siteEvent->getSite();
        return $site;
    }
}
