<?php
namespace Vivo\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * CurrentHostFactory
 * Returns name of the current host
 */
class CurrentHostFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $siteEvent \Vivo\SiteManager\Event\SiteEventInterface */
        $siteEvent      = $serviceLocator->get('Vivo\site_event');
        $host           = $siteEvent->getHost();
        return $host;
    }
}
