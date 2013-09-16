<?php
namespace Vivo\Db;

use Zend\Db\Adapter\Adapter as ZendDbAdapter;

use Doctrine\ORM\EntityManager;

use PDO;

/**
 * ModuleDbProvider
 * Provides db connection objects for a specific module in a specific site
 */
class ModuleDbProvider implements ModuleDbProviderInterface
{
    /**
     * Db service manager
     * @var DbServiceManagerInterface
     */
    protected $dbServiceManager;

    /**
     * Site config of the actual site
     * @var array
     */
    protected $siteConfig;

    /**
     * Constructor
     * @param DbServiceManagerInterface $dbServiceManager
     * @param array $siteConfig
     */
    public function __construct(DbServiceManagerInterface $dbServiceManager, array $siteConfig)
    {
        $this->dbServiceManager = $dbServiceManager;
        $this->siteConfig       = $siteConfig;
    }

    /**
     * Returns PDO object for the given module
     * @param string $module Module name
     * @return PDO
     */
    public function getPdo($module)
    {
        $dbSource   = $this->getDbSourceName($module);
        $pdo        = $this->dbServiceManager->getPdo($dbSource);
        return $pdo;
    }

    /**
     * Returns Zend DB Adapter object for the given module
     * @param string $module
     * @return ZendDbAdapter
     */
    public function getZendDbAdapter($module)
    {
        $dbSource   = $this->getDbSourceName($module);
        $zdba       = $this->dbServiceManager->getZendDbAdapter($dbSource);
        return $zdba;
    }

    /**
     * Returns Doctrine Entity Manager
     * @param string $module
     * @return EntityManager
     */
    public function getDoctrineEntityManager($module)
    {
        $dbSource   = $this->getDbSourceName($module);
        $dem        = $this->dbServiceManager->getDoctrineEntityManager($dbSource);
        return $dem;
    }

    /**
     * Returns db connection name for the given module
     * @param string $module Module name
     * @return string Db connection name
     * @throws Exception\InvalidArgumentException
     */
    protected function getDbSourceName($module)
    {
        //TODO - implement support for core modules
        if (!isset($this->siteConfig['modules']['site_modules'][$module]['db_source'])) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Key '[modules][site_modules][%s][db_source] not found in config'", __METHOD__, $module));
        }
        return $this->siteConfig['modules']['site_modules'][$module]['db_source'];
    }
}
