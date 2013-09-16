<?php
namespace Vivo\Db;

use Zend\Stdlib\ArrayUtils;

/**
 * DbTableNameProvider
 * Provides db table names
 */
class DbTableNameProvider
{
    /**
     * Options
     * @var array
     */
    protected $options   = array(
        'table_names'   => array(
            //Tables in Vivo Portal core
            'core'          => array(
            ),
            //Tables in modules (define in module config and override in a local config)
            'module'        => array(
    //            //Module name
    //            'MyModule'          => array(
    //                //Symbolic => real
    //                'symbolic_table_name'   => 'real_table_name',
    //            ),
            ),
        ),
    );

    /**
     * Constructor
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options   = ArrayUtils::merge($this->options, $options);
    }

    /**
     * Returns actual name of a db table
     * If mapping of the specified symbolic table name is not found, returns the symbolic table name unchanged
     * @param string $symbolicTableName
     * @param string|null $module Vivo module name, null = Vivo core
     * @return string
     */
    public function getTableName($symbolicTableName, $module = null)
    {
        if (is_null($module)) {
            //Vivo core mapping
            $map    = $this->options['table_names']['core'];
        } elseif (isset($this->options['table_names']['module'][$module])) {
            //Module mapping
            $map    = $this->options['table_names']['module'][$module];
        } else {
            //Module mapping not found
            $map    = array();
        }
        if (array_key_exists($symbolicTableName, $map)) {
            $tableName  = $map[$symbolicTableName];
        } else {
            $tableName  = $symbolicTableName;
        }
        return $tableName;
    }
}
