<?php
namespace Vivo\Db;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * DbTableNameProviderFactory
 */
class DbTableNameProviderFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\ConfigException
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config                 = $serviceLocator->get('config');
        if (isset($config['setup']['db']['table_name_provider'])
                && is_array($config['setup']['db']['table_name_provider'])) {
            $options    = $config['setup']['db']['table_name_provider'];
        } else {
            $options    = array();
        }
        $dbTableNameProvider    = new DbTableNameProvider($options);
        return $dbTableNameProvider;
    }
}
