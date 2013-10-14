<?php
namespace Vivo\Form;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * FactoryFactory
 * Form factory service manager factory
 */
class FactoryFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $inputFilterFactory     = $serviceLocator->get('input_filter_factory');
        $newInputFilterFactory  = $serviceLocator->get('Vivo\new_input_filter_factory');
        $formFactory            = new Factory();
        $formFactory->setInputFilterFactory($inputFilterFactory);
        $formFactory->setNewInputFilterFactory($newInputFilterFactory);
        return $formFactory;
    }
}
