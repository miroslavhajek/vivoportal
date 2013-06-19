<?php
namespace Vivo\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * ModuleResourceManagerFactory
 */
class ModuleResourceManagerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config                 = $serviceLocator->get('config');
        $resourceManagerOptions = $config['modules']['resource_manager'];
        $moduleStorageManager   = $serviceLocator->get('module_storage_manager');
        $moduleResourceManager  = new \Vivo\Module\ResourceManager\ResourceManager($moduleStorageManager,
                                        $resourceManagerOptions);
        //PerfLog
        $events = $serviceLocator->get('event_manager');
        $events->trigger('log', $this,
            array ('message'    => 'ModuleResourceManager created',
                'priority'   => \VpLogger\Log\Logger::PERF_FINER));
        return $moduleResourceManager;
    }
}
