<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\UI\Exception;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class SiteMapFactory implements FactoryInterface
{
    /**
     * Create UI Sitemap object.
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
        $cmsConfig  = $serviceLocator->get('cms_config');
        if (isset($cmsConfig['ui']['Vivo\UI\Content\SiteMap'])) {
            $uiCompConfig   = $cmsConfig['ui']['Vivo\UI\Content\SiteMap'];
        } else {
            $uiCompConfig   = array();
        }
        if (isset($uiCompConfig['cache'])) {
            $cacheMgr       = $serviceLocator->get('cache_manager');
            $cache  = $cacheMgr->get($uiCompConfig['cache']);
        } else {
            $cache  = null;
        }
        $service    = new SiteMap($cmsApi, $documentApi, $site, $cache);
        return $service;
    }
}
