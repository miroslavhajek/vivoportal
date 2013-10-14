<?php
namespace Vivo\Module;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * ModuleManagerFactoryFactory
 */
class ModuleManagerFactoryFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config                 = $serviceLocator->get('config');
        $modulePaths            = $config['modules']['module_paths'];
        $moduleStreamName       = $config['modules']['stream_name'];
        $application            = $serviceLocator->get('application');
        /* @var $application \Zend\Mvc\Application */
        $appEvents              = $application->getEventManager();
        $moduleManagerFactory   = new ModuleManagerFactory($modulePaths,
                                            $moduleStreamName,
                                            $appEvents,
                                            $serviceLocator);
        //PerfLog
        $appEvents->trigger('log', $this,
            array ('message'    => 'ModuleManagerFactory created',
                'priority'   => \VpLogger\Log\Logger::PERF_FINER));
        return $moduleManagerFactory;
    }
}
