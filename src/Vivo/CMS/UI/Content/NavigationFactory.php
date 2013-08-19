<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\UI\Exception;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class NavigationFactory implements FactoryInterface
{
    /**
     * Create UI Navigation object.
     * @param  ServiceLocatorInterface $serviceLocator
     * @throws \Vivo\CMS\UI\Exception\RuntimeException
     * @return \Zend\Stdlib\Message
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cmsApi         = $serviceLocator->get('Vivo\CMS\Api\CMS');
        $documentApi    = $serviceLocator->get('Vivo\CMS\Api\Document');
        /** @var $siteEvent \Vivo\SiteManager\Event\SiteEvent */
        $siteEvent      = $serviceLocator->get('site_event');
        $site           = $siteEvent->getSite();
        if (!$site) {
            throw new Exception\RuntimeException(sprintf("%s: Site model not available", __METHOD__));
        }
        $config     = $serviceLocator->get('config');
        if (isset($config['cache']['navigation'])) {
            $cacheMgr   = $serviceLocator->get('cache_manager');
            $cache      = $cacheMgr->get($config['cache']['navigation']);
        } else {
            $cache  = null;
        }
        $service    = new Navigation($cmsApi, $documentApi, $site, $cache);
        return $service;
    }
}
