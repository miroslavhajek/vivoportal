<?php
namespace Vivo\Db;

use Zend\Db\Adapter\Adapter as ZendDbAdapter;

use Doctrine\ORM\EntityManager;

use PDO;

/**
 * ModuleDbProviderInterface
 */
interface ModuleDbProviderInterface
{
    /**
     * Returns PDO object for the given module
     * @param string $module Module name
     * @return PDO
     */
    public function getPdo($module);

    /**
     * Returns Zend DB Adapter object for the given module
     * @param string $module
     * @return ZendDbAdapter
     */
    public function getZendDbAdapter($module);

    /**
     * Returns Doctrine Entity Manager
     * @param string $module
     * @return EntityManager
     */
    public function getDoctrineEntityManager($module);
}