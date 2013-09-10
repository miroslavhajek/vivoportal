<?php
namespace Vivo\View\Helper;

use Vivo\CMS\Util\ResourceUrlHelper;
use Zend\View\Helper\AbstractHelper;

/**
 * View helper for getting resource url.
 */
class Resource extends AbstractHelper
{

    /**
     * Resource url helper
     * @var ResourceUrlHelper
     */
    protected $resourceUrlHelper;

    /**
     * Constructor
     * @param ResourceUrlHelper $resourceUrlHelper
     */
    public function __construct(ResourceUrlHelper $resourceUrlHelper)
    {
        $this->resourceUrlHelper = $resourceUrlHelper;
    }

    /**
     * Builds resource URL
     * @see ResourceUrlHelper
     * @param string $resourcePath
     * @param string|Entity $source
     * @param array $options
     * @return string
     */
    public function __invoke($resourcePath, $source, array $options = array())
    {
        return $this->resourceUrlHelper->getResourceUrl($resourcePath, $source, $options);
    }

}
