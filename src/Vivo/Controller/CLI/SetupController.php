<?php
namespace Vivo\Controller\CLI;

use Vivo\Db\DbTableNameProvider;

use Zend\Db\Adapter\Adapter as ZendDbAdapter;

/**
 * Vivo CLI controller for command 'setup'
 */
class SetupController extends AbstractCliController
{
    const COMMAND = 'setup';

    /**
     * Zend DB adapter
     * @var ZendDbAdapter
     */
    protected $zdba;

    /**
     * DB Table name provider
     * @var DbTableNameProvider
     */
    protected $dbTableNameProvider;

    /**
     * Constructor
     * @param \Zend\Db\Adapter\Adapter $zdba
     * @param \Vivo\Db\DbTableNameProvider $dbTableNameProvider
     */
    public function __construct(ZendDbAdapter $zdba, DbTableNameProvider $dbTableNameProvider)
    {
        $this->zdba                 = $zdba;
        $this->dbTableNameProvider  = $dbTableNameProvider;
    }

    public function getConsoleUsage()
    {
        $output = "\nSetup usage:";
        $output .= "\n\nsetup db [--force|-f]";
        return $output;
    }

    /**
     * Sets up database
     * @return string
     */
    public function dbAction()
    {
        //Prepare params
        $request    = $this->getRequest();
        /* @var $request \Zend\Console\Request */
        // Check force flag
        $force      = $request->getParam('force') || $request->getParam('f');
        $output     = 'Db tables setup...';
        $tableNameUsers = $this->dbTableNameProvider->getTableName('vivo_users');
        if ($force) {
            //Remove tables if they exist
            $ddl    = sprintf("DROP TABLE IF EXISTS `%s`;", $tableNameUsers);
            $this->zdba->query($ddl, ZendDbAdapter::QUERY_MODE_EXECUTE);
            $output .= sprintf("\nTable '%s' dropped", $tableNameUsers);
        }
        //Create tables
        $ddl        = sprintf("CREATE TABLE IF NOT EXISTS `%s` (
                      `domain` varchar(60) NOT NULL DEFAULT 'GLOBAL',
                      `username` varchar(30) NOT NULL,
                      `password` varchar(100),
                      `fullname` varchar(50),
                      `email` varchar(60),
                      `active` tinyint(1) NOT NULL DEFAULT 1,
                      `expires` datetime,
                      PRIMARY KEY (`domain`, `username`));", $tableNameUsers);
        $this->zdba->query($ddl, ZendDbAdapter::QUERY_MODE_EXECUTE);
        $output .= sprintf("\nTable '%s' created", $tableNameUsers);
        return $output;
    }
}
