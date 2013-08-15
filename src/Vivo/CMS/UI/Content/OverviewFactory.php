<?php
namespace Vivo\CMS\UI\Content;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class OverviewFactory implements FactoryInterface
{
    /**
     * Create UI Overview object.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return \Zend\Stdlib\Message
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cms         = $serviceLocator->get('Vivo\CMS\Api\CMS');        
        $indexerApi  = $serviceLocator->get('Vivo\CMS\Api\Indexer');
        $documentApi = $serviceLocator->get('Vivo\CMS\Api\Document');
        /** @var $siteEvent \Vivo\SiteManager\Event\SiteEventInterface */
        $siteEvent   = $serviceLocator->get('site_event');
        $site        = $siteEvent->getSite();
        $cmsConfig   = $serviceLocator->get('cms_config');
        if (isset($cmsConfig['ui']['Vivo\UI\Content\Overview'])) {
            $uiCompConfig   = $cmsConfig['ui']['Vivo\UI\Content\Overview'];
        } else {
            $uiCompConfig   = array();
        }
        if (isset($uiCompConfig['cache'])) {
            $cacheMgr       = $serviceLocator->get('cache_manager');
            $cache  = $cacheMgr->get($uiCompConfig['cache']);
        } else {
            $cache  = null;
        }
        $service    = new Overview($cms, $indexerApi, $documentApi, $site, $cache);
        return $service;
    }
}
