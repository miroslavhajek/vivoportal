<?php
namespace Vivo\CMS\Util;

use Vivo\CMS\Api;
use Vivo\CMS\Model\Entity;
use Vivo\View\Helper\Exception\InvalidArgumentException;
use Vivo\Module\ResourceManager\ResourceManager as ModuleResourceManager;
use Vivo\Util\UrlHelper;

/**
 * View helper for getting resource url.
 */
class ResourceUrlHelper
{
    /**
     * Helper options
     * @var array
     */
    private $options = array(
        'check_resource'        => false, // useful for debugging sites
        //Path where Vivo resources are found
        'vivo_resource_path'    => null,
        //This maps current request route name to an appropriate route name for resources
        'resource_route_map'    => array(
            'vivo/cms'          => 'vivo/resource',
            'backend/cms'       => 'backend/resource',
            'backend/modules'   => 'backend/backend_resource',
            'backend/explorer'  => 'backend/backend_resource',
            'backend/other'     => 'backend/backend_resource',
            'backend/default'   => 'backend/backend_resource',
        ),
    );

    /**
     * CMS Api
     * @var Api\CMS
     */
    private $cmsApi;

    /**
     * Route name used for resources
     * @var string
     */
    protected $resourceRouteName;

    /**
     * Module Resource Manager
     * @var ModuleResourceManager
     */
    protected $moduleResourceManager;

    /**
     * Url helper
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * Constructor.
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param \Vivo\Module\ResourceManager\ResourceManager $moduleResourceManager
     * @param UrlHelper $urlHelper
     * @param string $currentRouteName
     * @param array $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(Api\CMS $cmsApi,
                                ModuleResourceManager $moduleResourceManager,
                                UrlHelper $urlHelper,
                                $currentRouteName,
                                $options = array())
    {
        $this->cmsApi                   = $cmsApi;
        $this->moduleResourceManager    = $moduleResourceManager;
        $this->urlHelper                = $urlHelper;
        $this->options                  = array_merge($this->options, $options);
        $this->resourceRouteName        = isset($this->options['resource_route_map'][$currentRouteName])
                                            ? $this->options['resource_route_map'][$currentRouteName] : '';
        if (!$this->options['vivo_resource_path']) {
            throw new Exception\InvalidArgumentException(sprintf("%s: 'vivo_resource_path' option not set",
                __METHOD__));
        }
    }

    /**
     * Builds resource URL
     * Adds mtime as query string param to enable correct reverse proxy cache invalidation
     * @example
     *      getResourceUrl('resource.jpg', $myDocument);
     *      getResourceUrl('images/page/logo.png', 'MyModule');
     * @param string $resourcePath
     * @param string|Entity $source
     * @param string|null $type Resource type (for module resources)
     * @param array $queryParams Query string parameters
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function getResourceUrl($resourcePath, $source, $type = null, array $queryParams = array())
    {
        if ($this->options['check_resource'] == true) {
            $this->checkResource($resourcePath, $source);
        }
        if ($source instanceof Entity) {
            $entityUrl          = $this->cmsApi->getEntityRelPath($source);
            $resourceRouteName  = $this->resourceRouteName . '_entity';
            $urlParams  = array(
                'path'      => $resourcePath,
                'entity'    => $entityUrl,
            );
            $mtime      = $this->cmsApi->getResourceMtime($source, $resourcePath);
            $reuseMatchedParams = true;
        } elseif (is_string($source)) {
            if ($source == 'Vivo') {
                //It is a Vivo resource
                $mtime  = $this->getVivoResourceMtime($resourcePath);
            } else {
                //It is a module resource
                $mtime  = $this->moduleResourceManager->getResourceMtime($source, $resourcePath, $type);
            }
            $resourceRouteName  = $this->resourceRouteName;
            $urlParams          = array(
                'source'    => $source,
                'path'      => $resourcePath,
                'type'      => 'resource',
            );
            $reuseMatchedParams = true;
        } else {
            throw new InvalidArgumentException(sprintf("%s: Invalid value for parameter 'source'.", __METHOD__));
        }
        $queryParams['mtime']   = $mtime;
        $urlOptions         = array(
            'query' => $queryParams,
        );
        $url = $this->urlHelper->fromRoute($resourceRouteName, $urlParams, $urlOptions, $reuseMatchedParams);
        //Replace encoded slashes in the url.
        //It's needed because apache returns 404 when the url contains encoded slashes
        //This behaviour could be changed in apache config, but it is not possible to do that in .htaccess context.
        //@see http://httpd.apache.org/docs/current/mod/core.html#allowencodedslashes
        $url = str_replace('%2F', '/', $url);
        return $url;
    }

    /**
     * Returns Vivo resource mtime or false when the resource does not exist
     * @param string $resourcePath Relative path to a Vivo resource
     * @return int|bool
     */
    protected function getVivoResourceMtime($resourcePath)
    {
        $vivoResourcePath   = $this->options['vivo_resource_path'] . '/' . $resourcePath;
        if (file_exists($vivoResourcePath)) {
            $mtime  = filemtime($vivoResourcePath);
        } else {
            $mtime  = false;
            //Log nonexistent resource
            $events = new \Zend\EventManager\EventManager();
            $events->trigger('log', $this,  array(
                'message'   => sprintf("Vivo resource '%s' not found", $vivoResourcePath),
                'priority'  => \VpLogger\Log\Logger::ERR,
            ));
        }
        return $mtime;
    }

    public function checkResource($resourcePath, $source)
    {
        //TODO check resource and throw exception if it doesn't exist.
    }
}
