<?php
namespace Vivo\CMS;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for FrontController
 */
class FrontControllerFactory implements FactoryInterface
{
    /**
     * Creates CMS front controller.
     * @param ServiceLocatorInterface $serviceLocator
     * @return FrontController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config = $sm->get('config');

        $fc = new FrontController($config['cms_front_controller']);
        $fc->setComponentTreeController($sm->get('component_tree_controller'));
        $fc->setCMS($sm->get('Vivo\CMS\Api\CMS'));
        $fc->setDocumentApi($sm->get('Vivo\CMS\Api\Document'));
        $fc->setSiteEvent($sm->get('site_event'));
        $fc->setCmsEvent($sm->get('cms_event'));
        $fc->setRedirector($sm->get('redirector'));
        $fc->setUrlHelper($sm->get('Vivo\Util\UrlHelper'));

        return $fc;
    }
}
