<?php
namespace Vivo\View\Helper;

use Vivo\CMS\Model\Entity;
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
     * @param string|null $type Resource type (for module resources)
     * @param array $queryParams Query string parameters
     * @return string
     */
    public function __invoke($resourcePath, $source, $type = null, array $queryParams = array())
    {
        return $this->resourceUrlHelper->getResourceUrl($resourcePath, $source, $type, $queryParams);
    }

}
