<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\Api\CMS as CmsApi;
use Vivo\CMS\Api\Document as DocumentApi;
use Vivo\CMS\Model\Site;
use Vivo\CMS\Model\Document;
use Vivo\CMS\Model\Content\Navigation as NavigationModel;
use Vivo\CMS\UI\Exception;

use Zend\Navigation\AbstractContainer as AbstractNavigationContainer;
use Zend\Navigation\Navigation as NavigationContainer;
use Zend\Cache\Storage\StorageInterface as Cache;

/**
 * Navigation UI component
 */
class Navigation extends AbstractNavigation
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
        if (!$this->content instanceof NavigationModel) {
            throw new Exception\DomainException(
                sprintf("%s: Content model must be of type 'Vivo\\CMS\\Model\\Content\\Navigation'", __METHOD__));
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
        switch ($this->navModel->getType()) {
            case \Vivo\CMS\Model\Content\Navigation::TYPE_ORIGIN:
                if (is_null($this->navModel->getOrigin())) {
                    //Origin not specified, use the current requested doc
                    $originPath = $this->cmsEvent->getRequestedPath();
                } else {
                    //Origin specified
                    $originPath = $this->navModel->getOrigin();
                }
                $keyParts   = array(
                    'requested_path'    => $this->cmsEvent->getRequestedPath(),
                    'origin_path'   => $originPath,
                    'start_level'   => $this->navModel->getStartLevel(),
                    'levels'        => $this->navModel->getLevels(),
                    'include_root'  => $this->navModel->includeRoot(),
                    'branch_only'   => $this->navModel->getBranchOnly(),
                );
                $key    = sha1(implode(',', $keyParts));
                break;
            case \Vivo\CMS\Model\Content\Navigation::TYPE_ENUM:
                $concat = $this->concatEnumeratedDocs($this->navModel->getEnumeratedDocs());
                $key    = sha1($concat);
                break;
            default:
                throw new Exception\RuntimeException(sprintf("%s: Unsupported navigation type '%s'",
                    __METHOD__, $this->navModel->getType()));
                break;
        }
        return $key;
    }

    /**
     * Concatenates paths of enumerated docs into a single string
     * @param array $enumeratedDocs
     * @return string
     */
    protected function concatEnumeratedDocs(array $enumeratedDocs)
    {
        $concat = '';
        foreach ($enumeratedDocs as $enumDoc) {
            $concat .= $enumDoc['doc_path'];
            if (isset($enumDoc['children'])) {
                $concat .= $this->concatEnumeratedDocs($enumDoc['children']);
            }
        }
        return $concat;
    }

    /**
     * Returns navigation container
     * @throws \Vivo\CMS\UI\Exception\DomainException
     * @return AbstractNavigationContainer
     */
    public function getNavigation()
    {
        if (is_null($this->navigation)) {
            switch ($this->navModel->getType()) {
                case NavigationModel::TYPE_ORIGIN:
                    if ($this->navModel->getOrigin()) {
                        //Origin explicitly specified
                        $origin = $this->cmsApi->getSiteEntity($this->navModel->getOrigin(), $this->site);
                    } else {
                        //Origin not specified, use the current doc
                        $origin = $this->cmsEvent->getDocument();
                    }
                    $rootDoc    = $this->getNavigationRoot($origin, $this->navModel->getStartLevel());
                    if ($rootDoc) {
                        //Root doc found
                        if ($this->navModel->getBranchOnly()) {
                            //Get only documents from a single branch (e.g. for breadcrumbs)
                            $documents  = $this->getActiveDocuments($this->cmsApi->getEntityRelPath($rootDoc),
                                                                    $this->navModel->includeRoot());
                        } else {
                            //GET all documents in a subtree
                            $documents  = $this->buildDocArray($rootDoc,
                                                               $this->navModel->getLevels(),
                                                               $this->navModel->includeRoot());
                        }
                    } else {
                        //Root doc not found
                        $documents  = array();
                    }
                    break;
                case NavigationModel::TYPE_ENUM:
                    $documents  = $this->navModel->getEnumeratedDocs();
                    break;
                default:
                    throw new Exception\DomainException(
                        sprintf("%s: Unsupported navigation type '%s'", __METHOD__, $this->navModel->getType()));
                    break;
            }
            //Create the navigation container
            $this->navigation   = new NavigationContainer();
            $pages              = $this->buildNavPages($documents, $this->content->getLimit());

            if($this->hasActiveDocument == false) {
                $currentPage = $this->cmsEvent->getDocument();
                $navigationContUuidTable = array();
                $navigationContUuidTable = $this->getFlatNavigationContainerPages($pages, $navigationContUuidTable);
                while($currentPage = $this->documentApi->getParentDocument($currentPage)) {
                    if(array_key_exists($currentPage->getUuid(), $navigationContUuidTable)) {
                        $navigationContUuidTable[$currentPage->getUuid()]->active = true;
                        $this->hasActiveDocument = true;
                        break;
                    }
                }
            }
            $this->navigation->setPages($pages);
        }
        return $this->navigation;
    }

    /**
     * Returns flat navigation container pages.
     *
     * @param array $pages
     * @param array $navigationContUuidTable
     * @return array
     */
    protected function getFlatNavigationContainerPages(array $pages, array $navigationContUuidTable)
    {
        foreach ($pages as $key => $page) {
            $navigationContUuidTable[$page->getUuid()] = $page;
            if($page->hasChildren()) {
                return $this->getFlatNavigationContainerPages($page->getPages(), $navigationContUuidTable);
            }
        }
        return $navigationContUuidTable;
    }

    /**
     * Returns document where the navigation starts
     * It is a document at the specified startLevel (+ means absolute level, - means relative level from $doc)
     * on the branch from root to $doc
     * If a document at the specified level does not exist, returns null
     * @param \Vivo\CMS\Model\Document $doc Document from which the navigation root document is calculated
     * @param int $startLevel
     * @return Document|null
     */
    public function getNavigationRoot(Document $doc, $startLevel)
    {
        if ($startLevel == 0) {
            //Start at the current doc
            $navRoot    = $doc;
        } else {
            $branchDocs     = $this->documentApi->getDocumentsOnBranch($doc, '/', true, true);
            if ($startLevel > 0) {
                //Start at $startLevel absolute level
                if (isset($branchDocs[$startLevel - 1])) {
                    //Document at the specified absolute level found on the current branch
                    $navRoot    = $branchDocs[$startLevel - 1];
                } else {
                    //Document at the specified level NOT found on the current branch
                    $navRoot    = null;
                }
            } else {
                //Start by -$startLevel levels up
                $absStartLevel  = count($branchDocs) + $startLevel - 1;
                if (isset($branchDocs[$absStartLevel])) {
                    $navRoot    = $branchDocs[$absStartLevel];
                } else {
                    $navRoot    = null;
                }
            }
        }
        return $navRoot;
    }

    /**
     * Builds document array only from documents on the active branch
     * @param string $rootDocPath
     * @param bool $includeRoot
     * @return array
     */
    protected function getActiveDocuments($rootDocPath, $includeRoot = false)
    {
        $currentDoc     = $this->cmsEvent->getDocument();
        $branchDocs     = $this->documentApi->getDocumentsOnBranch($currentDoc, $rootDocPath, $includeRoot, true);
        $branchDocsRev  = array_reverse($branchDocs);
        $docArray       = array();
        foreach ($branchDocsRev as $doc) {
            $docPath    = $this->cmsApi->getEntityRelPath($doc);
            $docArray   = array(
                array(
                    'doc_path'  => $docPath,
                    'children'  => $docArray,
                ),
            );
        }
        return $docArray;
    }

    /**
     * Method to be overriden.
     * Provides additional page options to CmsNavPage constructor
     * @return array
     */
    protected function getAdditionalPageOptions(Document $doc)
    {
        return array();
    }

    /**
     * Determines whether the document is allowed to be listed
     * @param Document $doc
     * @return bool
     */
    protected function allowListing(Document $doc)
    {
        return (bool) $doc->getAllowListingInNavigation() === false;
    }

    protected function sortDocuments($documents)
    {
        $sorting = $this->navModel->getNavigationSorting();
        if($sorting !== null && $sorting != 'none') {
            /* @var $currentDoc Document */
            $currentDoc = $this->cmsEvent->getDocument();
            $parentSorting = $currentDoc->getSorting();
            if(strpos($sorting, "parent") !== false && $parentSorting != null) {
                $sorting = $parentSorting;
            }
            $documents = $this->documentApi->sortDocumentsByCriteria($documents, $sorting);
        }

        return $documents;
    }

}
