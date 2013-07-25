<?php
namespace Vivo\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * RenderDocumentFactory
 */
class RenderDocumentFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\RuntimeException
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm                 = $serviceLocator->getServiceLocator();
        $documentApi        = $sm->get('Vivo\CMS\Api\Document');
        $componentFactory   = $sm->get('component_factory');
        $view               = $sm->get('view');
        $treeController     = $sm->get('component_tree_controller');
        $helper             = new RenderDocument($documentApi, $componentFactory, $view, $treeController);
        return $helper;
    }
}
