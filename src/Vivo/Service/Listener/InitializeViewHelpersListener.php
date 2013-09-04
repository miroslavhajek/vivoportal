<?php
namespace Vivo\Service\Listener;

use Vivo\View\Helper as ViewHelper;

use Zend\Mvc\MvcEvent;

/**
 * InitializeViewHelpersListener
 */
class InitializeViewHelpersListener
{
    /**
     * Initializes view helpers
     * @param MvcEvent $e
     */
    public function initializeViewHelpers($e) {
        $application    = $e->getTarget();
        $serviceLocator = $application->getServiceManager();
        $routeName      = $e->getRouteMatch()->getMatchedRouteName();
        /* @var $plugins \Zend\View\HelperPluginManager */
        $plugins        = $serviceLocator->get('view_helper_manager');

        //Set basepath for backend view
        if ($routeName == 'backend/cms') {
            /** @var $url ViewHelper\Url */
            $url        = $plugins->get('url');
            $path       = $url('backend/cms', array('path'=>''));
            $basePath   = $plugins->get('basepath');
            $basePath->setBasePath($path);
        }
    }
}
