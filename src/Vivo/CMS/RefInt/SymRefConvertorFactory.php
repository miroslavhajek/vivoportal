<?php
namespace Vivo\CMS\RefInt;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * SymRefConvertorFactory
 */
class SymRefConvertorFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cmsApi         = $serviceLocator->get('Vivo\CMS\Api\CMS');
        /** @var $siteEvent \Vivo\SiteManager\Event\SiteEvent */
        $siteEvent      = $serviceLocator->get('site_event');
        $uuidConvertor  = $serviceLocator->get('uuid_convertor');
        $site           = $siteEvent->getSite();
        $service        = new SymRefConvertor($cmsApi, $uuidConvertor, $site);
        return $service;
    }
}
