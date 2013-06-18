<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\Api\CMS as CmsApi;
use Vivo\CMS\Api\Document as DocumentApi;
use Vivo\CMS\Model\Site;
use Vivo\CMS\Model\Document;
use Vivo\CMS\Model\Content\Navigation as NavigationModel;
use Vivo\CMS\UI\Exception;
use Vivo\CMS\Navigation\Page\Cms as CmsNavPage;
use Vivo\CMS\UI\Component;
use Vivo\Repository\Exception\EntityNotFoundException;

use Zend\Navigation\AbstractContainer as AbstractNavigationContainer;
use Zend\Navigation\Navigation as NavigationContainer;
use Zend\Cache\Storage\StorageInterface as Cache;

/**
 * Navigation UI component
 */
class Navigation extends Component
{
    /**
     * CMS Api
     * @var CmsApi
     */
    protected $cmsApi;

    /**
     * Document API
     * @var DocumentApi
     */
    protected $documentApi;

    /**
     * Site model
     * @var Site
     */
    protected $site;

    /**
     * Navigation model (i.e. the content)
     * @var NavigationModel
     */
    protected $navModel;

    /**
     * Navigation
     * @var AbstractNavigationContainer
     */
    protected $navigation;

    /**
     * Cache for navigation view models
     * @var Cache
     */
    protected $cache;

    /**
     * Constructor
     * @param CmsApi $cmsApi
     * @param DocumentApi $documentApi
     * @param Site $site
     * @param \Zend\Cache\Storage\StorageInterface $cache
     */
    public function __construct(CmsApi $cmsApi, DocumentApi $documentApi, Site $site, Cache $cache = null)
    {
        $this->cmsApi       = $cmsApi;
        $this->documentApi  = $documentApi;
        $this->site         = $site;
        $this->cache        = $cache;
    }

    public function init()
    {
        if (!$this->content instanceof NavigationModel) {
            throw new Exception\DomainException(
                sprintf("%s: Content model must be of type 'Vivo\\CMS\\Model\\Content\\Navigation'", __METHOD__));
        }
        $this->navModel = $this->content;
    }

    public function view()
    {
        $events = new \Zend\EventManager\EventManager();
        $events->trigger('log', $this, array(
            'message'   => sprintf('Navigation::view() %s PRE', $this->getPath()),
            'priority'  => \VpLogger\Log\Logger::PERF_FINER));

        $viewModel  = $this->getViewModel();

        $events->trigger('log', $this, array(
            'message' => sprintf('Navigation::view() %s POST', $this->getPath()),
            'priority'  => \VpLogger\Log\Logger::PERF_FINER));
        return $viewModel;
    }

    /**
     * Returns view model
     * @return \Zend\View\Model\ModelInterface
     */
    protected function getViewModel()
    {
        //Get navigation container either from cache or construct it
        if ($this->cache) {
            $key        = $this->getCacheKey();
            $success    = null;
            $navigation = $this->cache->getItem($key, $success);
            if (!$success) {
                $navigation  = $this->getNavigation();
                $this->cache->setItem($key, $navigation);
            }
        } else {
            $navigation = $this->getNavigation();
        }
        //Prepare view
        $this->getView()->navigation    = $navigation;
        $viewModel  = parent::view();
        return $viewModel;
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
            $concat .= $enumDoc['docPath'];
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
            $this->navigation->setPages($pages);
        }
        return $this->navigation;
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
     * Builds document array starting at $root
     * @param Document $root Root document
     * @param int $levels
     * @param bool $includeRoot
     * @return array
     */
    protected function buildDocArray(Document $root, $levels = null, $includeRoot = false)
    {
        $docArray   = array();
        if (is_null($levels) || $levels > 0) {
            if (!is_null($levels)) {
                $levels--;
            }
            $children   = $this->documentApi->getChildDocuments($root);
            foreach ($children as $key => $child) {
                //TODO - Revise: Keep only documents, not folders
                if (!$child instanceof Document) {
                    unset($children[$key]);
                    continue;
                }
                $rec    = array(
                    'doc_path'  => $this->cmsApi->getEntityRelPath($child),
                    'children'  => $this->buildDocArray($child, $levels, false),
                );
                $docArray[] = $rec;
            }
            if ($includeRoot) {
                $docArray   = array(
                    array(
                        'doc_path'  => $root->getPath(),
                        'children'  => $docArray,
                    ),
                );
            }
        }
        return $docArray;
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
     * Builds navigation pages from the supplied documents structure
     * @param array $documents For structure see property Vivo\CMS\Model\Content::$enumeratedDocs
     * @param int $limit Number of documents listed in the navigation per level
     * @throws \Vivo\CMS\UI\Exception\UnexpectedValueException
     * @throws \Vivo\CMS\UI\Exception\InvalidArgumentException
     * @return CmsNavPage[]
     */
    protected function buildNavPages(array $documentsPaths = array(), $limit = null)
    {
        $pages      = array();
        $currentDoc = $this->cmsEvent->getDocument();        
        foreach($documentsPaths as $docArray) {
            if (!is_array($docArray)) {
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: Document record must be represented by an array", __METHOD__));
            }
            if (!array_key_exists('doc_path', $docArray)) {
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: Document array must contain 'doc_path' key", __METHOD__));
            }
            $docPath    = $docArray['doc_path'];
            try {
                $doc    = $this->cmsApi->getSiteEntity($docPath, $this->site);
            } catch (EntityNotFoundException $e) {
                $events = new \Zend\EventManager\EventManager();
                $events->trigger('log', $this, array (
                    'message' => $e->getMessage(), 
                    'level' => \VpLogger\Log\Logger::WARN));
                continue;
            }
            if (!$doc instanceof Document) {
                throw new Exception\UnexpectedValueException(
                    sprintf("%s: Entity specified by path '%s' is not a document", __METHOD__, $docPath));
            }
            $documents[] = array('doc' => $doc, 'children' => $docArray['children']);
        }        
        if($this->content->getNavigationSorting() !== null){
            $sorting = $this->content->getNavigationSorting();
            $parentSorting = $currentDoc->getSorting();
            if(strpos($sorting, "parent") !== false && $parentSorting != null) {
                $sorting = $parentSorting;
            }
            $documents = $this->documentApi->sortDocumentsByCriteria($documents, $sorting);
        }
        if($limit) {
            $documents = array_slice($documents, 0, $limit, true);
        }
        foreach ($documents as $key => $docArray) { 
            $doc = $docArray['doc'];
            $docRelPath     = $this->cmsApi->getEntityRelPath($doc);
            $pageOptions    = array(
                'sitePath'      => $docRelPath,
                'label'         => $doc->getNavigationTitle(),
                'active'        => $this->cmsApi->getEntityRelPath($currentDoc) == $docRelPath,
                'document'      => $doc,
            );
            $page           = new CmsNavPage($pageOptions);
            if ((bool) $doc->getAllowListingInNavigation() === false) {
                $page->visible = false;
            }
            if (array_key_exists('children', $docArray)
                    && is_array($docArray['children'])
                    && count($docArray['children']) > 0) {
                $children   = $this->buildNavPages($docArray['children'], $limit);
                $page->setPages($children);
            }
            if($this->documentApi->isPublished($doc)) {
                $pages[]    = $page;
            }
        }
        return $pages;
    }
}
