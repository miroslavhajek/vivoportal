<?php
namespace Vivo\Db\AbstractFactory;

use Vivo\Db\DbServiceManagerInterface;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter as ZendDba;
use Zend\Db\Adapter\Driver\Pdo\Pdo as ZendPdoDriver;

/**
 * ZendDbAdapter
 * Abstract factory to create Zend DB Adapter objects
 */
class ZendDbAdapter implements AbstractFactoryInterface
{
    /**
     * Configuration options
     * @var array
     */
    protected $options      = array(
        'service_identifier'    => 'zdb',
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
                          && $serviceLocator->hasDbService($connectionName);
        return $canCreate;
    }

    /**
     * Creates a Zend Db Adapter object and returns it
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return ZendDba
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var $serviceLocator  DbServiceManagerInterface */
        $connectionName = $serviceLocator->getConnectionNameFromServiceName($requestedName);
        $pdo            = $serviceLocator->getPdo($connectionName);
        $pdoDriver      = new ZendPdoDriver($pdo);
        $zdba           = new ZendDba($pdoDriver);
        return $zdba;
    }
}