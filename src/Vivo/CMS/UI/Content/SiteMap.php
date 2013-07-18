<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\Api\CMS as CmsApi;
use Vivo\CMS\Api\Document as DocumentApi;
use Vivo\CMS\Model\Document;
use Vivo\CMS\Model\Site;
use Vivo\CMS\Model\Content\SiteMap as SiteMapModel;
use Vivo\CMS\UI\Exception;

use Zend\Navigation\AbstractContainer as AbstractNavigationContainer;
use Zend\Navigation\Navigation as NavigationContainer;
use Zend\Cache\Storage\StorageInterface as Cache;

/**
 * SiteMap UI component
 */
class SiteMap extends AbstractNavigation
{
    /**
     * Constructor
     * @param CmsApi $cmsApi
     * @param DocumentApi $documentApi
     * @param Site $site
     * @param \Zend\Cache\Storage\StorageInterface $cache
     */
    public function __construct(CmsApi $cmsApi, DocumentApi $documentApi, Site $site, Cache $cache = null)
    {
        parent::__construct($cmsApi, $documentApi, $site, $cache);
    }

    public function init()
    {
        if (!$this->content instanceof SiteMapModel) {
            throw new Exception\DomainException(
                sprintf("%s: Content model must be of type 'Vivo\\CMS\\Model\\Content\\SiteMap'", __METHOD__));
        }
        $this->navModel = $this->content;
    }

    /**
     * Returns cache key used to cache the navigation container
     * @return string
     * @throws \Vivo\CMS\UI\Exception\RuntimeException
     */
    protected function getCacheKey()
    {
        $keyParts = array(
            'requested_path'    => $this->cmsEvent->getRequestedPath(),
            'origin' => $this->navModel->getOrigin(),
            'showDescription' => $this->navModel->getShowDescription(),
            'includeRoot' => $this->navModel->getIncludeRoot(),
        );
        $key = sha1(implode(',', $keyParts));
        return $key;
    }

    /**
     * Returns site map container
     * @throws \Vivo\CMS\UI\Exception\DomainException
     * @return AbstractNavigationContainer
     */
    public function getNavigation()
    {
        if (is_null($this->navigation)) {
            if ($this->navModel->getOrigin()) {
                //Origin explicitly specified
                $rootDoc = $this->cmsApi->getSiteEntity($this->navModel->getOrigin(), $this->site);
            } else {
                //Origin not specified, use the current doc
                $rootDoc = $this->cmsEvent->getDocument();
            }
            if ($rootDoc) {
                //GET all documents in a subtree
                // unlimited level
                // include root?
                $documents  = $this->buildDocArray($rootDoc,
                                                   null,
                                                   $this->navModel->getIncludeRoot());
            } else {
                //Root doc not found
                $documents  = array();
            }

            //Create the navigation container
            $this->navigation   = new NavigationContainer();
            $pages           = $this->buildNavPages($documents);

            $this->navigation->setPages($pages);
        }
        return $this->navigation;
    }

    /**
     * Provides additional page options to CmsNavPage constructor
     * @return array
     */
    protected function getAdditionalPageOptions(Document $doc)
    {
        return array(
            'showDescription'   => $this->navModel->getShowDescription(),
        );
    }

    /**
     * Determines whether the document is allowed to be listed
     * @param Document $doc
     * @return bool
     */
    protected function allowListing(Document $doc) {
        return (bool) $doc->getAllowListingInSiteMap() === false;
    }

}
