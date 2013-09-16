<?php
namespace Vivo\Db;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\RuntimeException;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;

use Doctrine\ORM\EntityManager;

use PDO;

/**
 * DbServiceManager
 * Provides DB services
 */
class DbServiceManager extends AbstractPluginManager implements DbServiceManagerInterface
{
    /**
     * Whether or not to auto-add a class as an invokable class if it exists
     * @var bool
     */
    protected $autoAddInvokableClass = false;

    /**
     * Returns connection name extracted from the service name
     * @param string $serviceName
     * @return string
     */
    public function getConnectionNameFromServiceName($serviceName)
    {
        $parts  = explode('_', $serviceName);
        array_pop($parts);
        $name   = implode('_', $parts);
        return $name;
    }

    /**
     * Returns type of connection (pdo|zdb|dem) from the service name
     * @param string $serviceName
     * @return string
     */
    public function getServiceTypeFromServiceName($serviceName)
    {
        $parts  = explode('_', $serviceName);
        $name   = array_pop($parts);
        return $name;
    }

    /**
     * Validate the plugin
     * @param  mixed $plugin
     * @return void
     * @throws RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof PDO
            || $plugin instanceof ZendDbAdapter
            || $plugin instanceof EntityManager) {
            // we're okay
            return;
        }
        throw new RuntimeException(sprintf(
            '%s: DB service of type %s is invalid; must be either a PDO, Zend\Db\Adapter\Adapter or '
            . 'Doctrine\ORM\EntityManager object',
            __METHOD__, (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }

    /**
     * Returns a PDO object representing the specified dbSource
     * @param string $dbSource
     * @return PDO
     */
    public function getPdo($dbSource)
    {
        return $this->get($dbSource . '_pdo');
    }

    /**
     * Returns Zend DB Adapter representing the specified dbSource
     * @param string $dbSource
     * @return ZendDbAdapter
     */
    public function getZendDbAdapter($dbSource)
    {
        return $this->get($dbSource . '_zdb');
    }

    /**
     * Returns Doctrine EntityManager representing the specified dbSource
     * @param $dbSource
     * @return EntityManager
     */
    public function getDoctrineEntityManager($dbSource)
    {
        return $this->get($dbSource . '_dem');
    }

    /**
     * Returns true when the specified service exists
     * @param string $connectionName Db service name WITHOUT the type suffix (_pdo|_zdb|_dem)
     * @return boolean
     */
    public function hasDbService($connectionName)
    {
        $realServiceName    = $connectionName . '_pdo';
        return $this->has($realServiceName);
    }
}