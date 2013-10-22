<?php
namespace Vivo\View\Helper;

use Vivo\Util\UrlHelper;
use Vivo\Module\ResourceManager\ResourceManager;

use Zend\View\Helper\HeadScript;
use Zend\Cache\Storage\StorageInterface as Cache;
use Zend\Stdlib\ArrayUtils;

use stdClass;

/**
 * HeadScriptMerge
 * Merges collected scripts into one file and caches the result
 */
class HeadScriptMerge extends HeadScript
{
    /**
     * Cache for merged files
     * @var Cache
     */
    protected $cache;

    /**
     * URL Helper
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * Resource Manager
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * Helper options
     * @var array
     */
    protected $options  = array(
        //Disable merging of scripts for debugging
        'merging_enabled'       => true,
        //Name of the route for access to resources
        'resource_route_name'   => 'vivo/resource',
        //Params of the route used to access cached data resources
        'cache_resource_route_params' => array(
            'source'    => 'Vivo',
            'type'      => 'cache',
            //'path' contains %s placeholder for sprintf, which will be replaced with cache key
            'path'      => 'head_script_merge/%s',
        ),
    );

    /**
     * Constructor
     * @param Cache $cache
     * @param UrlHelper $urlHelper
     * @param ResourceManager $resourceManager
     * @param array $options
     */
    public function __construct(Cache $cache,
                                UrlHelper $urlHelper,
                                ResourceManager $resourceManager,
                                array $options = array())
    {
        parent::__construct();
        $this->options          = ArrayUtils::merge($this->options, $options);
        $this->cache            = $cache;
        $this->urlHelper        = $urlHelper;
        $this->resourceManager  = $resourceManager;
    }

    /**
     * Retrieve string representation
     * @param  string|int $indent Amount of whitespaces or string to use for indention
     * @return string
     */
    public function toString($indent = null)
    {
        if ($this->options['merging_enabled']) {
            //Merging enabled
            $hash   = $this->getHash();
            if (is_null($hash)) {
                //No scripts in the container
                return '<!-- No valid entries in the HeadScriptMerge container -->';
            }
            $cacheKey   = $this->assembleCacheKey($hash);
            if (!$this->cache->hasItem($cacheKey)) {
                //Build merged file and store it to cache
                $merged = $this->buildMergedData();
                $this->cache->setItem($cacheKey, $merged);
            }

            $url    = $this->urlHelper->fromRoute($this->options['resource_route_name'], array(
                'source'    => $this->options['cache_resource_route_params']['source'],
                'type'      => $this->options['cache_resource_route_params']['type'],
                'path'      => sprintf($this->options['cache_resource_route_params']['path'], $cacheKey),
            ));

            $tag    = $this->assembleScriptTag($url, $indent);
            return $tag;
        } else {
            //Merging disabled
            /** @var $item stdClass */
            //Assemble 'src' attribute and call the parent implementation
            foreach ($this as $item) {
                if ($this->isValid($item)) {
                    $routeParams    = array(
                        'type'      => $item->attributes['rsc_mgr']['type'],
                        'path'      => $item->attributes['rsc_mgr']['path'],
                        'source'    => $item->attributes['rsc_mgr']['module'],
                    );
                    $url    = $this->urlHelper->fromRoute($this->options['resource_route_name'], $routeParams);
                    $item->attributes['src']    = $url;
                }
            }
            return parent::toString($indent);
        }
    }

    /**
     * Returns unique hash describing all paths and their mtimes
     * Returns null when the script container is empty
     * @throws Exception\RuntimeException
     * @return string|null
     */
    public function getHash()
    {
        $str    = '';
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            $scriptId   = $this->getScriptIdString($item);
            if (is_null($scriptId)) {
                throw new Exception\RuntimeException(sprintf("%s: Invalid script definition", __METHOD__));
            }
            $str    .= $scriptId . PHP_EOL;
        }
        if ($str == '') {
            //Empty container
            $hash   = null;
        } else {
            //At least one item in the container
            $hash   = hash('md4', $str);
        }
        return $hash;
    }

    /**
     * Is the script provided valid?
     * @param  mixed  $value  Is the given script valid?
     * @return bool
     */
    protected function isValid($value)
    {
        if ((!$value instanceof stdClass)
            || !isset($value->type)
            || !isset($value->attributes['rsc_mgr']['module'])
            || !isset($value->attributes['rsc_mgr']['type'])
            || !isset($value->attributes['rsc_mgr']['path'])
        ) {
            return false;
        }
        return true;
    }

    /**
     * Returns script id string or null when script object is not valid
     * @param stdClass $scriptObj
     * @return string|null
     * @throws Exception\RuntimeException
     */
    protected function getScriptIdString(stdClass $scriptObj)
    {
        if (!$this->isValid($scriptObj) || $scriptObj->type != 'text/javascript') {
            return null;
        }
        $mtime  = 0;
        if (isset($scriptObj->attributes['rsc_mgr'])) {
            //Script accessible via resource manager
            $rscManOpts = $scriptObj->attributes['rsc_mgr'];
            if (!isset($rscManOpts['type'])) {
                $rscManOpts['type'] = 'resource';
            }
            $mtime      = $this->resourceManager->getResourceMtime($rscManOpts['module'],
                                                                   $rscManOpts['path'],
                                                                   $rscManOpts['type']);
            if ($mtime === false) {
                //Resource not found
                throw new Exception\RuntimeException(
                    sprintf("%s: Resource manager could not find resource '%s' in module '%s' (type '%s')",
                        __METHOD__, $rscManOpts['path'], $rscManOpts['module'], $rscManOpts['type']));
            }
            $scriptId  = $rscManOpts['module'] . ',' . $rscManOpts['type'] . ',' . $rscManOpts['path'];
        } elseif (isset($scriptObj->attributes['src'])) {
            //Script accessible only via URL
            $scriptId  = $scriptObj->attributes['src'];
        } elseif (isset($scriptObj->source)) {
            //Script accessible only via URL
            $scriptId  = $scriptObj->source;
        } else {
            //Script source definition missing
            throw new Exception\RuntimeException(sprintf("%s: Source not defined", __METHOD__));
        }
        $scriptId   .= ',' . $mtime;
        return $scriptId;
    }

    /**
     * Returns cache key assembled from hash
     * @param string $hash
     * @return string
     */
    protected function assembleCacheKey($hash)
    {
        //Just add .js extension - the whole cache key is then considered a resource filename
        $cacheKey   = $hash . '.js';
        return $cacheKey;
    }

    /**
     * Assembles script tag from the given URL
     * @param string $url
     * @return string
     */
    protected function assembleScriptTag($url)
    {
        $tag    = sprintf('<script type="text/javascript" src="%s"></script>', $url);
        return $tag;
    }

    /**
     * Returns merged data
     * @return string
     * @throws Exception\RuntimeException
     */
    protected function buildMergedData()
    {
        $header     = sprintf('/* ***** Script files merged by Vivo Portal, %s ***** */', date('Y-m-d, H:i:s')) . PHP_EOL . PHP_EOL;
        $data       = '';
        $scriptInfo = '/* Merged script files (module, type, path):' . PHP_EOL . PHP_EOL;
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->isValid($item)) {
                //Script item not valid
                continue;
            }
            if ($item->type != 'text/javascript') {
                //Unsupported script type
                throw new Exception\RuntimeException(sprintf("%s: Unsupported script type '%s", __METHOD__, $item->type));
            }
            $module     = $item->attributes['rsc_mgr']['module'];
            $type       = $item->attributes['rsc_mgr']['type'];
            $path       = $item->attributes['rsc_mgr']['path'];
            $scriptInfo .= sprintf("'%s', '%s', '%s'", $module, $type, $path) . PHP_EOL;
            $data       .= sprintf("/* ***** Module: '%s', Type: '%s', Path: '%s' ***** */", $module, $type, $path)
                        . PHP_EOL . PHP_EOL;
            $itemData   = $this->resourceManager->getResource($module, $path, $type);
            $data       .= $itemData . PHP_EOL . PHP_EOL;
        }
        $scriptInfo     .= PHP_EOL . '*/'  . PHP_EOL . PHP_EOL;
        $merged         = $header . $scriptInfo . $data . '/* End of merged scripts */';
        return $merged;
    }
}
