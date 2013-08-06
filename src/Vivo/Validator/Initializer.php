<?php
namespace Vivo\Validator;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Initializer
 * Validator initializer
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
        $sm = $serviceLocator->getServiceLocator();
        if ($instance instanceof \Vivo\Service\Initializer\InputFilterFactoryAwareInterface) {
            $inputFilterFactory = $sm->get('input_filter_factory');
            $instance->setInputFilterFactory($inputFilterFactory);
        }
        if ($instance instanceof \Vivo\Service\Initializer\PathBuilderAwareInterface) {
            $pathBuilder = $sm->get('path_builder');
            $instance->setPathBuilder($pathBuilder);
        }
        if ($instance instanceof \Vivo\Service\Initializer\DocTitleToPathTransliteratorAwareInterface) {
            $docTitleToPathTransliterator = $sm->get('Vivo\Transliterator\DocTitleToPath');
            $instance->setTransliterator($docTitleToPathTransliterator);
        }
        if ($instance instanceof \Vivo\Service\Initializer\RepositoryAwareInterface) {
            $repository = $sm->get('repository');
            $instance->setRepository($repository);
        }
        if ($instance instanceof \Vivo\Service\Initializer\ValidatorPluginManagerAwareInterface) {
            $instance->setValidatorPluginManager($serviceLocator);
        }
    }
}
