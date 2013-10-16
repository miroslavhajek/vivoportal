<?php
namespace Vivo\View\Helper;

use Vivo\Util\UrlHelper;

use Zend\View\Helper\HeadScript;
use Zend\Cache\Storage\StorageInterface as Cache;

/**
 * HeadScriptMerging
 * Merges collected scripts into one file and caches the result
 */
class HeadScriptMerging extends HeadScript
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
     * Constructor
     * @param Cache $cache
     * @param \Vivo\Util\UrlHelper $urlHelper
     */
    public function __construct(Cache $cache, UrlHelper $urlHelper)
    {
        parent::__construct();
        $this->cache        = $cache;
        $this->urlHelper    = $urlHelper;
    }

    public function xtoString($indent = null)
    {
        $hash   = $this->getHash();
        //hash = getResourcesHash()
        //if !cache has hash, build merged file and store to cache
        //assemble merged file url
        //return script tag linking the cached merged file




        $return = implode($this->getSeparator(), $items);
        return $return;
    }

    /**
     * Returns unique hash describing all paths and their mtimes
     * Returns null when the script container is empty
     * @return string|null
     */
    public function getHash()
    {
        $items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->isValid($item)) {
                continue;
            }

//            $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
        }

        return 'FOO Barr';
    }

    protected function assembleScriptTag($hash, $indent)
    {
        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();
        if ($this->view) {
            $useCdata   = $this->view->plugin('doctype')->isXhtml() ? true : false;
        } else {
            $useCdata   = $this->useCdata ? true : false;
        }
        $escapeStart    = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd      = ($useCdata) ? '//]]>' : '//-->';


        $tag            = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);

    }
}
