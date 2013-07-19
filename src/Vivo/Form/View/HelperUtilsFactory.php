<?php
namespace Vivo\Form\View;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * HelperUtilsFactory
 */
class HelperUtilsFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $helperUtils    = new HelperUtils();
        return $helperUtils;
    }
}
