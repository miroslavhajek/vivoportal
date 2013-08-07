<?php
namespace Vivo\InputFilter\Condition;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Initializer
 * Input filter condition initializer
 */
class Initializer implements InitializerInterface
{
    /**
     * Initialize
     * @param $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof \Vivo\Service\Initializer\InputFilterFactoryAwareInterface) {
            $sm                 = $serviceLocator->getServiceLocator();
            $inputFilterFactory = $sm->get('input_filter_factory');
            $instance->setInputFilterFactory($inputFilterFactory);
        }
        if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
            $instance->setServiceLocator($serviceLocator);
        }
    }
}
