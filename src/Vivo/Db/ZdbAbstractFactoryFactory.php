<?php
namespace Vivo\Db;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * ZdbAbstractFactoryFactory
 */
class ZdbAbstractFactoryFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config     = $serviceLocator->get('config');
        $zdbConfig  = $config['db_service']['abstract_factory']['zdb'];
        $zdbAf      = new \Vivo\Db\AbstractFactory\ZendDbAdapter($zdbConfig);
        return $zdbAf;
    }
}
