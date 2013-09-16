<?php
namespace Vivo\Db;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * PdoAbstractFactoryFactory
 */
class PdoAbstractFactoryFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->get('config');
        $pdoConfig      = $config['db_service']['abstract_factory']['pdo'];
        $pdoAf          = new \Vivo\Db\AbstractFactory\Pdo($pdoConfig);
        return $pdoAf;
    }
}
