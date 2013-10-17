<?php
namespace Vivo\View\Helper;

use Vivo\Util\UrlHelper;
use Vivo\Module\ResourceManager\ResourceManager;

use Zend\View\Helper\HeadScript;
use Zend\Cache\Storage\StorageInterface as Cache;

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
     * Constructor
     * @param Cache $cache
     * @param UrlHelper $urlHelper
     * @param ResourceManager $resourceManager
     */
    public function __construct(Cache $cache, UrlHelper $urlHelper,  ResourceManager $resourceManager)
    {
        parent::__construct();
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
        $hash   = $this->getHash();
        if (is_null($hash)) {
            //No scripts in the container
            return '<!-- No valid entries in the HeadScriptMerge container -->';
        }
        $cacheKey   = $this->assembleCacheKey($hash);
        if (!$this->cache->hasItem($cacheKey)) {
            //Build merged file and store it to cache

        }

        //TODO - route params configurable?
        $url    = $this->urlHelper->fromRoute('vivo/resource', array(
            'source'    => 'Vivo',
            'type'      => 'cache',
            'path'      => 'head_script_merge/' . $cacheKey,
        ));

        $tag    = $this->assembleScriptTag($url, $indent);
        return $tag;

        //hash = getResourcesHash()
        //if !cache has hash, build merged file and store to cache
        //assemble merged file url
        //return script tag linking the cached merged file
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

    protected function buildMergedFile()
    {
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->isValid($item)) {
                continue;
            }
            if ($item->type != 'text/javascript') {
                //Unsupported script type
            }
            if (isset($item->attributes['src'])) {
                $scriptUrl  = $item->attributes['src'];
            } elseif (isset($item->source)) {
                $scriptUrl  = $item->source;
            } else {
                throw new Exception\RuntimeException(sprintf("%s: Source not defined", __METHOD__));
            }


            //TODO - mtime!
            $mtime  = 0;

            $str    .= $scriptUrl . ',' . $mtime . PHP_EOL;
        }

    }
}
