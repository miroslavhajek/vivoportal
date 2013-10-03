<?php
namespace Vivo\Service;

use Zend\Mvc\Router\RouteMatch;

/**
 * RouteMatchService
 * Provides current RouteMatch object or null when routing has not occurred yet
 * The RouteMatch object must be injected using the setRouteMatch() setter!
 */
class RouteMatchService
{
    /**
     * Route Match object
     * @var RouteMatch
     */
    protected $routeMatch;

    /**
     * Sets RouteMatch object
     * @param \Zend\Mvc\Router\RouteMatch $routeMatch
     */
    public function setRouteMatch(RouteMatch $routeMatch)
    {
        $this->routeMatch = $routeMatch;
    }

    /**
     * Returns RouteMatch object
     * @return \Zend\Mvc\Router\RouteMatch|null
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }


}
