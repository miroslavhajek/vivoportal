<?php
namespace Vivo\CMS\Util;

use Vivo\CMS\Api;
use Vivo\CMS\Model\Entity;
use Vivo\Module\ResourceManager\ResourceManager as ModuleResourceManager;
use Vivo\Util\UrlHelper;

use Zend\Stdlib\ArrayUtils;

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
        //This maps current request route name to an appropriate route name for Vivo and VModule resources
        'resource_route_map'    => array(
            'vivo/cms'                  => 'vivo/resource',
            'vivo/resource'             => 'vivo/resource',
            'vivo/resource_entity'      => 'vivo/resource',
            'backend/other'             => 'backend/backend_resource',
            'backend/default'           => 'backend/backend_resource',
            'backend/modules'           => 'backend/backend_resource',
            'backend/explorer'          => 'backend/backend_resource',
            'backend/backend_resource'  => 'backend/backend_resource',
            'backend/cms'               => 'backend/resource',
            'backend/resource'          => 'backend/resource',
            'backend/resource_entity'   => 'backend/resource',
        ),
        //This maps current request route name to an appropriate route name for entity resources
        'entity_resource_route_map' => array(
            'vivo/cms'                  => 'vivo/resource_entity',
            'vivo/resource'             => 'vivo/resource_entity',
            'vivo/resource_entity'      => 'vivo/resource_entity',
            'backend/other'             => 'vivo/resource_entity',
            'backend/default'           => 'vivo/resource_entity',
            'backend/modules'           => 'vivo/resource_entity',
            'backend/explorer'          => 'vivo/resource_entity',
            'backend/backend_resource'  => 'vivo/resource_entity',
            'backend/cms'               => 'backend/resource_entity',
            'backend/resource'          => 'backend/resource_entity',
            'backend/resource_entity'   => 'backend/resource_entity',
        ),
    );

    /**
     * CMS Api
     * @var Api\CMS
     */
    private $cmsApi;

    /**
     * Route name used for Vivo and VModule resources
     * @var string
     */
    protected $resourceRouteName;

    /**
     * Route name used for entity resources
     * @var string
     */
    protected $entityResourceRouteName;

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
     * @throws Exception\RuntimeException
     */
    public function __construct(Api\CMS $cmsApi,
                                ModuleResourceManager $moduleResourceManager,
                                UrlHelper $urlHelper,
                                $currentRouteName,
                                array $options = array())
    {
        $this->cmsApi                   = $cmsApi;
        $this->moduleResourceManager    = $moduleResourceManager;
        $this->urlHelper                = $urlHelper;
        $this->options                  = ArrayUtils::merge($this->options, $options);
        if (!isset($this->options['resource_route_map'][$currentRouteName])) {
            throw new Exception\RuntimeException(
                sprintf("%s: 'resource_route_map' does not contain an entry for '%s' route name",
                    __METHOD__, $currentRouteName));
        }
        if (!isset($this->options['entity_resource_route_map'][$currentRouteName])) {
            throw new Exception\RuntimeException(
                sprintf("%s: 'entity_resource_route_map' does not contain an entry for '%s' route name",
                    __METHOD__, $currentRouteName));
        }
        $this->resourceRouteName        = $this->options['resource_route_map'][$currentRouteName];
        $this->entityResourceRouteName  = $this->options['entity_resource_route_map'][$currentRouteName];
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
     * @param array $options Options
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function getResourceUrl($resourcePath, $source, array $options = array())
    {
        if ($this->options['check_resource'] == true) {
            $this->checkResource($resourcePath, $source);
        }
        if ($source instanceof Entity) {
            //Entity resource
            $entityUrl          = $this->cmsApi->getEntityRelPath($source);
            $resourceRouteName  = $this->entityResourceRouteName;
            $urlParams  = array(
                'path'      => $resourcePath,
                'entity'    => $entityUrl,
            );
            $mtime      = $this->cmsApi->getResourceMtime($source, $resourcePath);
        } elseif (is_string($source)) {
            //Module or Vivo resource
            if ($source == 'Vivo') {
                //It is a Vivo resource
                $mtime  = $this->getVivoResourceMtime($resourcePath);
            } else {
                //It is a module resource
                $type = isset($options['type']) ? $options['type'] : null;
                $mtime  = $this->moduleResourceManager->getResourceMtime($source, $resourcePath, $type);
            }
            $resourceRouteName  = $this->resourceRouteName;
            $urlParams          = array(
                'source'    => $source,
                'path'      => $resourcePath,
                'type'      => 'resource',
            );
        } else {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Invalid value for parameter 'source'", __METHOD__));
        }
        $options['query']['mtime'] = $mtime;
        $options['reuse_matched_params'] = true;
        return $this->urlHelper->fromRoute($resourceRouteName, $urlParams, $options);
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
