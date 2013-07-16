<?php
namespace Vivo\Backend\UI\Explorer;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Explorer factory
 */
class ExplorerFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->get('service_manager');
        $siteSelector = $sm->get('Vivo\Backend\UI\SiteSelector');

        $urlHelper = $sm->get('Vivo\Util\UrlHelper');

        // get uuid from route (from path param)
        /** @var Zend\Mvc\Router\Http\RouteMatch $routeMatch */
        $routeMatch = $serviceLocator->get('site_event')->getRouteMatch();
        $uuid = $routeMatch->getParam('path');
        $explorerAction = $routeMatch->getParam('explorerAction') ?: 'browser';

        $explorer = new \Vivo\Backend\UI\Explorer\Explorer($sm->get('Vivo\CMS\Api\CMS'),
                $siteSelector, $sm, $urlHelper, $uuid, $explorerAction);
        $explorer->setComponentTreeController($serviceLocator->get('component_tree_controller'));

        $explorer->setEventManager($sm->get('event_manager'));

        $explorer->addComponent($sm->create('Vivo\Backend\UI\Explorer\Ribbon'), 'ribbon');

        $cmsConfig  = $sm->get('cms_config');
        if (isset($cmsConfig['backend']['tree']['options'])) {
            $options    = $cmsConfig['backend']['tree']['options'];
        } else {
            $options    = array();
        }
        $tree = new \Vivo\Backend\UI\Explorer\Tree(
                $sm->get('Vivo\CMS\Api\CMS'),
                $sm->get('Vivo\CMS\Api\Document'),
                $options);
        $tree->setView($sm->get('view_model'));
        $tree->setExplorer($explorer);
        $explorer->addComponent($tree, 'tree');

        $finder = $sm->get('Vivo\Backend\UI\Explorer\Finder');
        $finder->setExplorer($explorer);
        $explorer->addComponent($finder, 'finder');

        return $explorer;
    }
}
