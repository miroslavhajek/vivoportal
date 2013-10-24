<?php
namespace Vivo\View\Helper;

use Vivo\Util\UrlHelper;
use Vivo\Module\ResourceManager\ResourceManager;

use Zend\View\Exception;
use Zend\View\Helper\HeadScript;
use Zend\Cache\Storage\StorageInterface as Cache;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Helper\Placeholder\Container\AbstractContainer;

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
     * Container for scripts in resources and direct script code
     * @var AbstractContainer
     */
    protected $mergeContainer;

    /**
     * Key under which is merge container registered in the registry
     * @var string
     */
    protected $mergeContainerKey = 'Vivo_View_Helper_HeadScriptMerge';

    /**
     * Helper options
     * @var array
     */
    protected $options  = array(
        //Disable merging of scripts for debugging
        'merging_enabled'       => true,
        //When set to true, the merged js file will be regenerated upon every request (useful for debugging)
        'always_regenerate'     => false,
        //When set to true, .min.js file will be used instead of the defined one, if exists
        'use_min_file'          => true,
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
        $this->mergeContainer   = $this->getRegistry()->getContainer($this->mergeContainerKey);
        $this->cache            = $cache;
        $this->urlHelper        = $urlHelper;
        $this->resourceManager  = $resourceManager;
    }

    /**
     * Prepends script from a resource
     * @param string $module
     * @param string $path
     * @param string $type
     * @throws Exception\InvalidArgumentException
     */
    public function prependResource($module, $path, $type = 'resource')
    {
        $scriptObj  = $this->createScriptObjForResource($module, $path, $type);
        if (!$this->isValidForMerge($scriptObj)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Script resource not valid", __METHOD__));
        }
        $this->mergeContainer->prepend($scriptObj);
    }

    /**
     * Appends script from a resource
     * @param string $module
     * @param string $path
     * @param string $type
     * @throws Exception\InvalidArgumentException
     */
    public function appendResource($module, $path, $type = 'resource')
    {
        $scriptObj  = $this->createScriptObjForResource($module, $path, $type);
        if (!$this->isValidForMerge($scriptObj)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Script resource not valid", __METHOD__));
        }
        $this->mergeContainer->append($scriptObj);
    }

    /**
     * Appends script source to the container
     * @param string $content
     * @param string $type
     * @param null $attrs
     * @throws Exception\InvalidArgumentException
     */
    public function appendScript($content, $type, $attrs = null)
    {
        $scriptObj  = $this->createScriptObjForSource($content);
        if (!$this->isValidForMerge($scriptObj)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Script source not valid", __METHOD__));
        }
        $this->mergeContainer->append($scriptObj);
    }

    /**
     * Prepends script source to the container
     * @param string $content
     * @param string $type
     * @param null $attrs
     * @throws Exception\InvalidArgumentException
     */
    public function prependScript($content, $type, $attrs = null)
    {
        $scriptObj  = $this->createScriptObjForSource($content);
        if (!$this->isValidForMerge($scriptObj)) {
            throw new Exception\InvalidArgumentException(sprintf("%s: Script source not valid", __METHOD__));
        }
        $this->mergeContainer->prepend($scriptObj);
    }

    /**
     * Creates and returns a stdClass object representing script saved in a module resource
     * @param string $module
     * @param string $path
     * @param string $type
     * @return stdClass
     */
    protected function createScriptObjForResource($module, $path, $type)
    {
        $scriptObj              = new \stdClass();
        $scriptObj->type        = 'text/javascript';
        $scriptObj->source      = null;
        $scriptObj->attributes  = array(
            'rsc_mgr'   => array(
                'module'    => $module,
                'type'      => $type,
                'path'      => $path,
            ),
        );
        return $scriptObj;
    }

    /**
     * Creates and returns a stdClass object representing script source code
     * @param string $source Script source code
     * @return stdClass
     */
    protected function createScriptObjForSource($source)
    {
        $scriptObj              = new \stdClass();
        $scriptObj->type        = 'text/javascript';
        $scriptObj->source      = $source;
        return $scriptObj;
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
            $hash   = $this->getHash($this->mergeContainer);
            if (!is_null($hash)) {
                $cacheKey   = $this->assembleCacheKey($hash);
                if ($this->options['always_regenerate'] || !$this->cache->hasItem($cacheKey)) {
                    //Build merged file and store it to cache
                    $merged = $this->buildMergedData($this->mergeContainer);
                    $this->cache->setItem($cacheKey, $merged);
                }
                $url    = $this->urlHelper->fromRoute($this->options['resource_route_name'], array(
                    'source'    => $this->options['cache_resource_route_params']['source'],
                    'type'      => $this->options['cache_resource_route_params']['type'],
                    'path'      => sprintf($this->options['cache_resource_route_params']['path'], $cacheKey),
                ));
                $this->appendFile($url);
            }
        } else {
            //Merging disabled - append items from the merging container to the original container
            /** @var $item stdClass */
            foreach ($this->mergeContainer as $item) {
                if (isset($item->attributes['rsc_mgr'])) {
                    //Script defined as resource
                    $routeParams    = array(
                        'type'      => $item->attributes['rsc_mgr']['type'],
                        'path'      => $item->attributes['rsc_mgr']['path'],
                        'source'    => $item->attributes['rsc_mgr']['module'],
                    );
                    $url        = $this->urlHelper->fromRoute($this->options['resource_route_name'], $routeParams);
                    $attribs    = array(
                        'src'   => $url,
                    );
                    $data       = $this->createData($item->type, $attribs);
                    parent::append($data);
                } elseif (isset($item->source)) {
                    //Script source code defined directly
                    $data       = $this->createData($item->type, array(), $item->source);
                    parent::append($data);
                }
            }
        }
        $html   = parent::toString($indent);
        return $html;
    }

    /**
     * Returns unique hash describing all paths and their mtimes
     * Returns null when the script container does not contain any cacheable scripts
     * @param \Zend\View\Helper\Placeholder\Container\AbstractContainer $container
     * @return string|null
     */
    public function getHash(AbstractContainer $container)
    {
        $str    = '';
        $container->ksort();
        foreach ($container as $item) {
            $scriptId   = $this->getScriptIdString($item);
            if (!is_null($scriptId)) {
                $str    .= $scriptId . PHP_EOL;
            }
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
    protected function isValidForMerge($value)
    {
        if (($value instanceof stdClass)
            && isset($value->type)
            && (isset($value->source)
                || (isset($value->attributes['rsc_mgr']['module'])
                    && isset($value->attributes['rsc_mgr']['type'])
                    && isset($value->attributes['rsc_mgr']['path'])))
        ) {
            return true;
        }
        return false;
    }

    /**
     * Returns script id string
     * Return null when:
     * a) script object is not valid
     * b) script resource is not found
     * @param stdClass $scriptObj
     * @throws Exception\RuntimeException
     * @return string|null
     */
    protected function getScriptIdString(stdClass $scriptObj)
    {
        if (!$this->isValidForMerge($scriptObj) || $scriptObj->type != 'text/javascript') {
            return null;
        }
        if (isset($scriptObj->attributes['rsc_mgr'])) {
            //Script accessible via resource manager
            $rscManOpts = $scriptObj->attributes['rsc_mgr'];
            if (!isset($rscManOpts['type'])) {
                $rscManOpts['type'] = 'resource';
            }
            $module     = $rscManOpts['module'];
            $path       = $rscManOpts['path'];
            $type       = $rscManOpts['type'];
            if ($this->options['use_min_file']) {
                $path   = $this->getMinPath($module, $path, $type);
            }
            if ($this->resourceManager->resourceExists($module, $path, $type)) {
                $mtime      = $this->resourceManager->getResourceMtime($module, $path, $type);
                $scriptId   = $module . ',' . $type . ',' . $path;
                $scriptId   .= ',' . $mtime;
            } else {
                //Resource not found
                $scriptId   = null;
            }
        } elseif (isset($scriptObj->source)) {
            //Script source code defined directly
            $scriptId  = $scriptObj->source;
        } else {
            //This should be never reached due to isValidForMerge() checking
            $scriptId   = null;
        }
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
     *
     * @obsolete
     */
    protected function assembleScriptTag($url)
    {
        $tag    = sprintf('<script type="text/javascript" src="%s"></script>', $url);
        return $tag;
    }

    /**
     * Returns merged data
     * @param \Zend\View\Helper\Placeholder\Container\AbstractContainer $container
     * @return string
     */
    protected function buildMergedData(AbstractContainer $container)
    {
        $header         = sprintf('/* ***** Script files merged by Vivo Portal, %s ***** */', date('Y-m-d, H:i:s'))
                        . PHP_EOL;
        if ($this->options['use_min_file']) {
            $header     .= sprintf('/* Using minified versions if available */') . PHP_EOL;
        }
        $header         .= PHP_EOL;
        $data           = '';
        $scriptInfo     = '/* Merged script files (module, type, path):' . PHP_EOL . PHP_EOL;
        $container->ksort();
        $scDirectCount  = 0;
        foreach ($container as $item) {
            if (!$this->isValidForMerge($item) || $item->type != 'text/javascript') {
                //Script item not valid
                continue;
            }
            if (isset($item->attributes['rsc_mgr'])) {
                //Script defined as resource
                $module     = $item->attributes['rsc_mgr']['module'];
                $type       = $item->attributes['rsc_mgr']['type'];
                $path       = $item->attributes['rsc_mgr']['path'];
                if ($this->options['use_min_file']) {
                    $path   = $this->getMinPath($module, $path, $type);
                }
                $scriptInfo .= sprintf("'%s', '%s', '%s'", $module, $type, $path) . PHP_EOL;
                $data       .= sprintf("/* ***** Module: '%s', Type: '%s', Path: '%s' ***** */", $module, $type, $path)
                            . PHP_EOL . PHP_EOL;
                $itemData   = $this->resourceManager->getResource($module, $path, $type);
                $data       .= $itemData . PHP_EOL . PHP_EOL;
            } elseif (isset($item->source)) {
                //Script source code defined directly
                $scDirectCount++;
                $scriptInfo .= sprintf('Script source code defined directly (%s)', $scDirectCount) . PHP_EOL;
                $data       .= sprintf('/* ***** Directly defined script source code (%s) ***** */', $scDirectCount)
                            . PHP_EOL . PHP_EOL;
                $data       .= $item->source . PHP_EOL . PHP_EOL;
            } else {
                //This should be never reached due to isValid() checking
            }
        }
        $scriptInfo     .= PHP_EOL . '*/'  . PHP_EOL . PHP_EOL;
        $merged         = $header . $scriptInfo . $data . '/* End of merged scripts */';
        return $merged;
    }

    /**
     * Checks if a minified version of the given script exists and returns its path, otherwise returns the original path
     * @param string $module
     * @param string $path
     * @param string $type
     * @return string
     */
    protected function getMinPath($module, $path, $type)
    {
        $pathElems      = explode('.', $path);
        $ext            = array_pop($pathElems);
        $pathElems[]    = 'min';
        $pathElems[]    = $ext;
        $pathMin        = implode('.', $pathElems);
        if ($this->resourceManager->resourceExists($module, $pathMin, $type)) {
            $path   = $pathMin;
        }
        return $path;
    }

    public function append($value)
    {
        if ($this->isValidForMerge($value)) {
            $this->mergeContainer->append($value);
        } else {
            parent::append($value);
        }
    }

    public function prepend($value)
    {
        if ($this->isValidForMerge($value)) {
            $this->mergeContainer->prepend($value);
        } else {
            parent::prepend($value);
        }
    }
}
