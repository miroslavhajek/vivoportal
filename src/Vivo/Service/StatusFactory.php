<?php
namespace Vivo\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * StatusFactory
 */
class StatusFactory implements FactoryInterface
{
    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $routeMatchService RouteMatchService */
        $routeMatchService  = $serviceLocator->get('Vivo\route_match_service');
        $routeMatch         = $routeMatchService->getRouteMatch();
        $request            = $serviceLocator->get('request');
        $status             = new Status($routeMatch, $request);
        return $status;
    }
}
