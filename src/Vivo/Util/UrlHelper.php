<?php
namespace Vivo\Util;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Helper class for assembling urls.
 *
 * Has the same function as \Zend\View\Helper\Url, it only modifies reused params.
 * Helper always reuses 'path' and 'host' router match param and never reuses 'controller' param.
 * Moreover, if the route name is 'backend/explorer', the helper always reuses 'explorerAction' param.
 */
class UrlHelper
{

    /**
     * @var RouteMatch
     */
    private $routeMatch;

    /**
     * @var RouteStackInterface
     */
    private $router;

    /**
     * Host name
     * @var string
     */
    protected $host;

    /**
     * Default options
     * @var array
     */
    protected $options = array(
        'reuse_matched_params' => false,
        'full_url' => false,    // return full url (incl. scheme and port if other than default)
        'settings' => array(
            'secured' => false, // http or https?
            'ports' => array(   // ports the vivoportal is running on
                'http'  => 80,
                'https' => 443,
            ),
        )
    );

    /**
     * Reasonable defaults for default ports
     * @var array
     */
    protected $defaultPorts = array(
        'http'  => 80,
        'https' => 443,
    );

    /**
     * Constructor.
     * @param RouteStackInterface $router
     * @param RouteMatch $routeMatch
     */
    public function __construct(RouteStackInterface $router, RouteMatch $routeMatch, $host, $options)
    {
        $this->router = $router;
        $this->routeMatch = $routeMatch;
        $this->host = $host;
        // $this->options['settings']['ports'] are here overwritten by local.config
        $this->options = ArrayUtils::merge($this->options, $options);
    }

    /**
     * Returns port
     * Returns string in format ':[port_number]' if vivoportal is NOT running on default port,
     * otherwise returns empty string
     * @param bool $isSecured
     * @return string
     */
    protected function getPort($isSecured) {
        $scheme = $isSecured ? 'https' : 'http';
        $port = '';
        if ($this->options['settings']['ports'][$scheme] != $this->defaultPorts[$scheme]) {
            $port = ':'.$this->options['settings']['ports'][$scheme];
        }
        return $port;
    }

    /**
     *
     * @param array $keys
     * @param array $array
     */
    private function unsetKeys(array $keys, array &$array) {
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                unset($array[$key]);
            }
        }
    }

    /**
     * Assemble url using route and router params.
     *
     * @param string $route
     * @param array $params Route params
     * @param array $options
     * @return string
     * @throws \RuntimeException
     */
    public function fromRoute($route = null, array $params = array(), array $options = array())
    {
        $localOptions = ArrayUtils::merge($this->options, (array) $options);
        $secured = $localOptions['settings']['secured'];
        $fullUrl = $localOptions['full_url'];

        if ($route === null) {
            if (!$this->routeMatch) {
                throw new \RuntimeException('No RouteMatch instance present');
            }

            $route = $this->routeMatch->getMatchedRouteName();

            if ($route === null) {
                throw new \RuntimeException('RouteMatch does not contain a matched route name');
            }
        }

        if ($localOptions['reuse_matched_params'] && $this->routeMatch) {
            $routeMatchParams = $this->routeMatch->getParams();

            if (isset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
                $routeMatchParams['controller'] = $routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
                unset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
            }

            if (isset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE])) {
                unset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE]);
            }

            $params = array_merge($routeMatchParams, $params);
        }

        $localOptions['name'] = $route;
        if (!isset($params['host']))
            $params['host'] =  $this->routeMatch->getParam('host');
        if (!isset($params['path']))
            $params['path'] = $this->routeMatch->getParam('path');

        if (($route == 'backend/explorer') && (!isset($params['explorerAction']))) {
            $params['explorerAction'] = $this->routeMatch->getParam('explorerAction');
        }

        $keysToRemove = array('full_url', 'settings', 'reuse_matched_params', 'type');
        $this->unsetKeys($keysToRemove, $localOptions);

        $url = $this->router->assemble($params, $localOptions);

        if ($fullUrl) {
            $url = sprintf('%s%s%s%s',
                        ($secured ? 'https://' : 'http://'),
                        $this->host,
                        $this->getPort($secured),
                        $url);
        }

        //Replace encoded slashes in the url.
        //It's needed because apache returns 404 when the url contains encoded slashes
        //This behaviour could be changed in apache config, but it is not possible to do that in .htaccess context.
        //@see http://httpd.apache.org/docs/current/mod/core.html#allowencodedslashes
        return str_replace('%2F', '/', $url);
    }
}
