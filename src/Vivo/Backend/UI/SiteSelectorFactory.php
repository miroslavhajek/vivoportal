<?php
namespace Vivo\Backend\UI;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Factory for CMSFrontController
 */
class SiteSelectorFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $siteApi \Vivo\CMS\Api\Site */
        $siteApi = $serviceLocator->get('Vivo\CMS\Api\Site');
        /* @var $siteEvent \Vivo\SiteManager\Event\SiteEvent */
        $siteEvent = $serviceLocator->get('site_event');
        /* @var $site \Vivo\CMS\Model\Site */
        $site = $siteEvent->getSite();
        $sites = $siteApi->getManageableSites();

        $siteSelector = new SiteSelector($site, $sites);
        return $siteSelector;
    }
}
