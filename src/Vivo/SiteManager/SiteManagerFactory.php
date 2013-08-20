<?php
namespace Vivo\SiteManager;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * SiteManagerFactory
 */
class SiteManagerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config                 = $serviceLocator->get('config');
        $siteEvents             = $serviceLocator->get('event_manager');
        $coreModules            = $config['modules']['core_modules'];
        $siteEvent              = $serviceLocator->get('site_event');
        $routeParamHost         = 'host';
        $moduleManagerFactory   = $serviceLocator->get('module_manager_factory');
        $moduleStorageManager   = $serviceLocator->get('module_storage_manager');
        $siteApi                = $serviceLocator->get('Vivo\CMS\Api\Site');
        $moduleResourceManager  = $serviceLocator->get('module_resource_manager');
        $metadataManager        = $serviceLocator->get('metadata_manager');
        $siteManager            = new SiteManager($siteEvents,
            $siteEvent,
            $routeParamHost,
            $moduleManagerFactory,
            $coreModules,
            $moduleStorageManager,
            $siteApi,
            $serviceLocator,
            $moduleResourceManager,
            null,
            $metadataManager
        );

        //PerfLog
        $siteEvents->trigger('log', $this,
            array ('message'    => 'SiteManager created',
                'priority'   => \VpLogger\Log\Logger::PERF_FINER));
        return $siteManager;
    }
}
