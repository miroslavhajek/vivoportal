<?php
namespace Vivo\Form;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * NewFormFactoryFactory
 */
class NewFormFactoryFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $formFactory            = $serviceLocator->get('Vivo\form_factory');
        $newInputFilterFactory  = $serviceLocator->get('Vivo\new_input_filter_factory');
        $newFormFactory         = new NewFormFactory($formFactory, $newInputFilterFactory);
        return $newFormFactory;
    }
}
