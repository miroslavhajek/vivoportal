<?php
namespace Vivo\View\Helper;

use Vivo;

/**
 * Url view helper.
 *
 * Has the same function as \Zend\View\Helper\Url, it only modifies reused params.
 * Helper always reuses 'path' and 'host' router match param and never reuses 'controller' param.
 */
class Url extends \Zend\View\Helper\AbstractHelper
{
    /**
     *
     * @var Vivo\Util\UrlHelper
     */
    protected $urlHelper;

    /**
     * Constructor
     * @param Vivo\Util\UrlHelper $urlHelper
     */
    public function __construct($urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * Assemble url using route and router params.
     * @param string|null $name Route name
     * @param array $params
     * @param array $options
     * @param bool $reuseMatchedParams
     * @return string
     */
    public function __invoke($name = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        $route = $this->urlHelper->fromRoute($name, $params, $options, $reuseMatchedParams);
        return $route;
    }
}
