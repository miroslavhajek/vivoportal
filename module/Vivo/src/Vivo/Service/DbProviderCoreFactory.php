<?php
namespace Vivo\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * DbProviderCoreFactory
 * Creates DB Provider for database used by Vivo core
 */
class DbProviderCoreFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\ConfigException
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $dbProviderFactory \Vivo\Service\DbProviderFactory */
        $dbProviderFactory  = $serviceLocator->get('db_provider_factory');
        $config             = $serviceLocator->get('config');
        if (!isset($config['setup']['db_source'])) {
            throw new Exception\ConfigException(sprintf("%s: Config key ['setup']['db_source'] missing", __METHOD__));
        }
        $dbProvider         = $dbProviderFactory->getDbProvider($config['setup']['db_source']);
        return $dbProvider;
    }
}
