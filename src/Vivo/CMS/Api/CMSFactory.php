<?php
namespace Vivo\CMS\Api;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * CMSFactory
 */
class CMSFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $repository     = $serviceLocator->get('repository');
        $uuidConvertor  = $serviceLocator->get('uuid_convertor');
        $uuidGenerator  = $serviceLocator->get('uuid_generator');
        $pathBuilder    = $serviceLocator->get('path_builder');
        $cms            = new CMS($repository,
                                  $uuidConvertor,
                                  $uuidGenerator,
                                  $pathBuilder);
        return $cms;
    }
}
