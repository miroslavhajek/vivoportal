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
        $config     = $serviceLocator->get('config');
        if (isset($config['cache']['overview'])) {
            $cacheMgr   = $serviceLocator->get('cache_manager');
            $cache      = $cacheMgr->get($config['cache']['overview']);
        } else {
            $cache  = null;
        }
        $service    = new Overview($cms, $indexerApi, $documentApi, $site, $cache);
        return $service;
    }
}
