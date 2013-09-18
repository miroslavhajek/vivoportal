<?php
namespace Vivo\Db;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;

/**
 * DbTableGatewayProvider
 * Provides db table gateways
 */
class DbTableGatewayProvider
{
    /**
     * Module Db provider
     * @var ModuleDbProviderInterface
     */
    protected $moduleDbProvider;

    /**
     * Zend DB Adapter for Vivo core
     * @var ZendDbAdapter
     */
    protected $coreZdba;

    /**
     * Table name provider
     * @var DbTableNameProvider
     */
    protected $tableNameProvider;

    /**
     * Table gateways instantiated so far
     * @var array
     */
    protected $tblGws = array(
        'core'      => array(),
        'module'    => array(),
    );

    /**
     * Constructor
     * @param ModuleDbProviderInterface $moduleDbProvider
     * @param ZendDbAdapter $coreZdba
     * @param DbTableNameProvider $tableNameProvider
     */
    public function __construct(ModuleDbProviderInterface $moduleDbProvider,
                                ZendDbAdapter $coreZdba,
                                DbTableNameProvider $tableNameProvider)
    {
        $this->moduleDbProvider     = $moduleDbProvider;
        $this->coreZdba             = $coreZdba;
        $this->tableNameProvider    = $tableNameProvider;
    }

    /**
     * Returns table gateway for the given symbolic name
     * @param string $symbolicTableName
     * @param string|null $module Module name, null = Vivo core
     * @throws Exception\InvalidArgumentException
     * @return TableGateway
     */
    public function get($symbolicTableName, $module = null)
    {
        if (is_null($module)) {
            //Vivo core table
            if (!isset($this->tblGws['core'][$symbolicTableName])) {
                $this->tblGws['core'][$symbolicTableName]   = $this->doGet($symbolicTableName, $module);
            }
            $tgw    = $this->tblGws['core'][$symbolicTableName];
        } else {
            //Module table
            if (!isset($this->tblGws['module'][$module][$symbolicTableName])) {
                $this->tblGws['module'][$module][$symbolicTableName]    = $this->doGet($symbolicTableName, $module);
            }
            $tgw    = $this->tblGws['module'][$module][$symbolicTableName];
        }
        return $tgw;
    }

    /**
     * Creates table gateway object
     * @param string $symbolicTableName
     * @param string|null $module
     * @return TableGateway
     */
    protected function doGet($symbolicTableName, $module = null)
    {
        $realTableName  = $this->tableNameProvider->getTableName($symbolicTableName, $module);
        if (is_null($module)) {
            $zdba   = $this->coreZdba;
        } else {
            $zdba   = $this->moduleDbProvider->getZendDbAdapter($module);
        }
        $tgw    = new TableGateway($realTableName, $zdba);
        return $tgw;
    }
}
