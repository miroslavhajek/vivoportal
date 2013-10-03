<?php
namespace Vivo\Service;

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
     * RouteMatch object
     * @var RouteMatch
     */
    protected $routeMatch;

    /**
     * Request
     * @var RequestInterface
     */
    protected $request;

    /**
     * Flag indicating if backend is running
     * @var bool
     */
    private $isBackend;

    /**
     * Constructor
     * @param RouteMatch $routeMatch
     * @param RequestInterface $request
     */
    public function __construct(RouteMatch $routeMatch, RequestInterface $request)
    {
        $this->routeMatch   = $routeMatch;
        $this->request      = $request;
    }

    /**
     * Returns true when backend is active, otherwise returns false
     * @return bool
     */
    public function isBackend()
    {
        if (is_null($this->isBackend)) {
            $routeName  = $this->getCurrentRouteName();
            if ((strpos($routeName, 'backend/') === 0) && ($routeName != 'backend/cms')) {
                $this->isBackend  = true;
            } else {
                $this->isBackend  = false;
            }
        }
        return $this->isBackend;
    }

    /**
     * Returns name of the current route
     * @throws Exception\RuntimeException
     * @return string
     */
    public function getCurrentRouteName()
    {
        return $this->routeMatch->getMatchedRouteName();
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
