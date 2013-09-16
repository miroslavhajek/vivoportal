<?php
namespace Vivo\Db;

use Zend\Db\Adapter\Adapter as ZendDbAdapter;

use Doctrine\ORM\EntityManager;

use PDO;

/**
 * DbProvider
 * Provides various types of db access objects for a configured db source
 */
class DbProvider implements DbProviderInterface
{
    /**
     * Db source name
     * @var string
     */
    protected $dbSource;

    /**
     * Db Service Manager
     * @var DbServiceManagerInterface
     */
    protected $dbServiceManager;

    /**
     * Constructor
     * @param DbServiceManagerInterface $dbServiceManager
     * @param string $dbSource
     * @throws Exception\DbSourceDoesNotExistException
     */
    public function __construct(DbServiceManagerInterface $dbServiceManager, $dbSource)
    {
        $this->dbServiceManager = $dbServiceManager;
        $this->dbSource         = $dbSource;
        //Check that the specified db source exists
        if (!$this->dbServiceManager->hasDbService($dbSource)) {
            throw new Exception\DbSourceDoesNotExistException(
                sprintf("%s: Db source '%s' does not exist", __METHOD__, $dbSource));
        }
    }

    /**
     * Returns PDO object
     * @return PDO
     */
    public function getPdo()
    {
        return $this->dbServiceManager->getPdo($this->dbSource);
    }

    /**
     * Returns Zend DB Adapter
     * @return ZendDbAdapter
     */
    public function getZendDbAdapter()
    {
        return $this->dbServiceManager->getZendDbAdapter($this->dbSource);
    }

    /**
     * Returns Doctrine Entity Manager
     * @return EntityManager
     */
    public function getDoctrineEntityManager()
    {
        return $this->dbServiceManager->getDoctrineEntityManager($this->dbSource);
    }
}