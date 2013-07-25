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
     * @param array $params Route params
     * @param array $options
     * @return string
     */
    public function __invoke($name = null, array $params = array(), array $options = array())
    {
        $route = $this->urlHelper->fromRoute($name, $params, $options);
        return $route;
    }
}
