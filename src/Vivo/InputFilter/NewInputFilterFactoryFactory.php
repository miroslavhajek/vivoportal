<?php
namespace Vivo\InputFilter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * NewInputFilterFactoryFactory
 */
class NewInputFilterFactoryFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $inputFilterFactory     = $serviceLocator->get('Vivo\input_filter_factory');
        $newInputFilterFactory  = new NewInputFilterFactory($inputFilterFactory);
        return $newInputFilterFactory;
    }
}
