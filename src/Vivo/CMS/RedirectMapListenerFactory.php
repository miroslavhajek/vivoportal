<?php
namespace Vivo\CMS;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for RedirectMapListener
 */
class RedirectMapListenerFactory implements FactoryInterface
{
    /**
     * Creates service.
     * @param ServiceLocatorInterface $serviceLocator
     * @return RedirectMapListener
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        $listener = new RedirectMapListener($sm->get('Vivo\CMS\Api\CMS'));
        return $listener;
    }
}
