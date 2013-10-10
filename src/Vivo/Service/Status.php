<?php
namespace Vivo\Service;

use Vivo\Service\RouteMatchService;

use Zend\Mvc\Router\RouteMatch;
use Zend\Stdlib\RequestInterface;
use Zend\Console\Request as ConsoleRequest;

/**
 * Status
 * Provides status info about the running Vivo Portal application
 */
class Status
{
    /**
     * RouteMatch service
     * @var RouteMatchService
     */
    protected $routeMatchService;

    /**
     * Request
     * @var RequestInterface
     */
    protected $request;

    /**
     * RouteMatch object
     * @var RouteMatch
     */
    private $routeMatch;

    /**
     * Flag indicating if backend is running
     * @var bool
     */
    private $isBackend;

    /**
     * Name of the current route
     * @var string|null
     */
    private $currentRouteName;

    /**
     * Constructor
     * @param RouteMatchService $routeMatchService
     * @param RequestInterface $request
     */
    public function __construct(RouteMatchService $routeMatchService, RequestInterface $request)
    {
        $this->routeMatchService    = $routeMatchService;
        $this->request              = $request;
    }

    /**
     * Returns RouteMatch object
     * @return null|RouteMatch
     */
    protected function getRouteMatch()
    {
        if (is_null($this->routeMatch)) {
            $this->routeMatch   = $this->routeMatchService->getRouteMatch();
        }
        return $this->routeMatch;
    }

    /**
     * Returns name of the current route
     * @return string|null
     */
    public function getCurrentRouteName()
    {
        if (is_null($this->currentRouteName)) {
            $routeMatch = $this->getRouteMatch();
            if (!is_null($routeMatch)) {
                $this->currentRouteName = $routeMatch->getMatchedRouteName();
            }
        }
        return $this->currentRouteName;
    }

    /**
     * Returns true when backend is active, otherwise returns false
     * @return bool
     */
    public function isBackend()
    {
        if (is_null($this->isBackend)) {
            $routeName  = $this->getCurrentRouteName();
            if (!is_null($routeName) && (strpos($routeName, 'backend/') === 0) && ($routeName != 'backend/cms')) {
                $this->isBackend  = true;
            } else {
                $this->isBackend  = false;
            }
        }
        return $this->isBackend;
    }

    /**
     * Returns if the current request is made from console
     * @return bool
     */
    public function isConsoleRequest()
    {
        if ($this->request instanceof ConsoleRequest) {
            $isConsoleRequest   = true;
        } else {
            $isConsoleRequest   = false;
        }
        return $isConsoleRequest;
    }
}
