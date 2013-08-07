<?php
namespace Vivo\CMS\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ComponentTreeFromDocumentListenerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ComponentTreeFromDocumentListener($serviceLocator->get('component_factory'));
    }

}
