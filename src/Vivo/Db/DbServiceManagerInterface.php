<?php
namespace Vivo\Db;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;

use Doctrine\ORM\EntityManager;

use PDO;


/**
 * DbServiceManagerInterface
 */
interface DbServiceManagerInterface extends ServiceLocatorInterface
{
    /**
     * Returns a PDO object representing the specified dbSource
     * @param string $dbSource
     * @return PDO
     */
    public function getPdo($dbSource);

    /**
     * Returns Zend DB Adapter representing the specified dbSource
     * @param string $dbSource
     * @return ZendDbAdapter
     */
    public function getZendDbAdapter($dbSource);

    /**
     * Returns Doctrine EntityManager representing the specified dbSource
     * @param $dbSource
     * @return EntityManager
     */
    public function getDoctrineEntityManager($dbSource);

    /**
     * Returns connection name extracted from the service name
     * @param string $serviceName
     * @return string
     */
    public function getConnectionNameFromServiceName($serviceName);

    /**
     * Returns type of connection (pdo|doctrine) from the service name
     * @param string $serviceName
     * @return string
     */
    public function getServiceTypeFromServiceName($serviceName);

    /**
     * Returns true when the specified service exists
     * @param string $connectionName Db connection name WITHOUT the type suffix (_pdo|_zdb|_dem)
     * @return boolean
     */
    public function hasDbService($connectionName);
}