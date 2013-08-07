<?php
namespace Vivo\Util;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * UrlHelper factory
 */
class UrlHelperFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return UrlHelper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $router = $serviceLocator->get('router');
        $routeMatch = $serviceLocator->get('application')->getMvcEvent()->getRouteMatch();

        // get host
        $siteEvent = $serviceLocator->get('site_event');
        $host = $siteEvent->getHost();

        // load configuration of default ports
        $config = $serviceLocator->get('config');
        $portConfig = array();
        if (isset($config['setup']['ports']) && is_array($config['setup']['ports'])) {
            $portConfig = $config['setup']['ports'];
        }
        $options['settings'] = array('ports' => $portConfig);


        $urlHelper = new UrlHelper($router, $routeMatch, $host, $options);
        return $urlHelper;
    }
}
