<?php
namespace Vivo\Db;

/**
 * DbProviderFactory
 * Creates and returns DbProvider objects
 * Caches created instances
 */
class DbProviderFactory
{
    /**
     * Db Service Manager
     * @var DbServiceManagerInterface
     */
    protected $dbServiceManager;

    /**
     * Array of DbProviders created so far
     * @var DbProviderInterface[]
     */
    protected $instances    = array();

    /**
     * Constructor
     * @param DbServiceManagerInterface $dbServiceManager
     */
    public function __construct(DbServiceManagerInterface $dbServiceManager)
    {
        $this->dbServiceManager = $dbServiceManager;
    }

    /**
     * Creates and returns a DbProvider
     * @param string $dbSource
     * @return DbProvider
     */
    public function getDbProvider($dbSource)
    {
        if (!array_key_exists($dbSource, $this->instances)) {
            $this->instances[$dbSource] = new DbProvider($this->dbServiceManager, $dbSource);
        }
        return $this->instances[$dbSource];
    }
}
