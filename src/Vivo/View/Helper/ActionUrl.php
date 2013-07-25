<?php
namespace Vivo\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * View helper for getting action url
 */
class ActionUrl extends AbstractHelper
{

    /**
     *
     * @param string $action
     * @param array $queryArgs
     * @param bool $reuseMatchedParams
     * @param array $routeParams Array of parameters passed to route
     * @return string
     */
    public function __invoke($action,
                             array $queryArgs = array(),
                             $reuseMatchedParams = false,
                             array $routeParams = array()
    ) {
        $actionHelper   = $this->view->plugin('action');
        $act            = $actionHelper($action);
        $options        = array(
            'query' => array(
                'act' => $act,
                'args' => $queryArgs,
            ),
        );
        $urlParams  = $routeParams;
        $urlHelper  = $this->getView()->plugin('url');
        $url        = $urlHelper(null, $urlParams, $options, $reuseMatchedParams);
        return $url;
    }
}
