<?php
namespace Vivo\View\Helper;

use Vivo;

/**
 * Url view helper.
 *
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
