<?php
namespace Vivo\Db;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * DbServiceManagerFactory
 * Instantiates the DbServiceManager
 */
class DbServiceManagerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dbSm   = new DbServiceManager();
        $pdoAf  = $serviceLocator->get('pdo_abstract_factory');
        $dbSm->addAbstractFactory($pdoAf);
        $zdbAf  = $serviceLocator->get('zdb_abstract_factory');
        $dbSm->addAbstractFactory($zdbAf);
        return $dbSm;
    }
}