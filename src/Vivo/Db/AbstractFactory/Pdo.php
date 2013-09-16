<?php
namespace Vivo\Db\AbstractFactory;

use Vivo\Db\DbServiceManagerInterface;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use \PDO as PdoConn;

/**
 * Pdo
 * Abstract factory to create PDO objects for the registered PDO configs
 */
class Pdo implements AbstractFactoryInterface
{
    /**
     * Configuration options
     * @var array
     */
    protected $options      = array(
        'service_identifier'    => 'pdo',
        'config'                => array(),
    );

    /**
     * Constructor
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options  = array_merge($this->options, $options);
    }

    /**
     * Determine if we can create a service with name
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var $serviceLocator  DbServiceManagerInterface */
        $serviceId      = $serviceLocator->getServiceTypeFromServiceName($requestedName);
        $connectionName = $serviceLocator->getConnectionNameFromServiceName($requestedName);
        $canCreate      = ($serviceId == $this->options['service_identifier'])
                          && array_key_exists($connectionName, $this->options['config']);
        return $canCreate;
    }

    /**
     * Creates a PDO object and returns it
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return \PDO
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var $serviceLocator  DbServiceManagerInterface */
        $connectionName = $serviceLocator->getConnectionNameFromServiceName($requestedName);
        $config         = $this->options['config'][$connectionName];
        if (isset($config['username'])) {
            $username   = $config['username'];
        } else {
            $username   = null;
        }
        if (isset($config['password'])) {
            $password   = $config['password'];
        } else {
            $password   = null;
        }
        if (isset($config['options'])) {
            $options    = $config['options'];
        } else {
            $options    = null;
        }
        $pdo    = new PdoConn($config['dsn'], $username, $password, $options);
        return $pdo;
    }
}