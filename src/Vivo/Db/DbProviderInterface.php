<?php
namespace Vivo\Db;

use Zend\Db\Adapter\Adapter as ZendDbAdapter;

use Doctrine\ORM\EntityManager;

use PDO;

/**
 * DbProviderInterface
 * Returns various types of db connection objects
 */
interface DbProviderInterface
{
    /**
     * Returns PDO object
     * @return PDO
     */
    public function getPdo();

    /**
     * Returns Zend DB Adapter
     * @return ZendDbAdapter
     */
    public function getZendDbAdapter();

    /**
     * Returns Doctrine Entity Manager
     * @return EntityManager
     */
    public function getDoctrineEntityManager();
}
