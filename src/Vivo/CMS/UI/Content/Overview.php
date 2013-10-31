<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\Api\CMS;
use Vivo\CMS\Api\Document as DocumentApi;
use Vivo\CMS\Api\Indexer as IndexerApi;
use Vivo\CMS\Model\Entity;
use Vivo\CMS\Model\Document;
use Vivo\CMS\Model\Site;
use Vivo\CMS\Model\Content\Overview as OverviewModel;
use Vivo\CMS\UI\Component;
use Vivo\CMS\UI\Exception\Exception;
use Vivo\CMS\UI\Exception\RuntimeException;
use Vivo\CMS\RefInt\SymRefConvertorInterface;
use Vivo\Repository\Exception\EntityNotFoundException;
use Vivo\UI\ComponentEventInterface;

use Zend\Cache\Storage\StorageInterface as Cache;

/**
 * Overview UI component
 *
 * Overview displays list of subpages (sub-documents) or other designated
 * documents. Typically is used to create reports and menus.
 */
class Overview extends Component
{
    /**
     * @var \Vivo\CMS\Api\CMS
     */
    private $cmsApi;

    /**
     * Indexer API
     * @var IndexerApi
     */
    protected $indexerApi;

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
     * Cache for overview documents
     * @var Cache
     */
    protected $cache;

    /**
     * Constructor
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param \Vivo\CMS\Api\Indexer $indexerApi
     * @param \Vivo\CMS\Api\Document $documentApi
     * @param \Vivo\CMS\Model\Site $site
     * @param \Zend\Cache\Storage\StorageInterface $cache
     */
    public function __construct(CMS $cmsApi,
                                IndexerApi $indexerApi,
                                DocumentApi $documentApi,
                                Site $site,
                                Cache $cache = null)
    {
        $this->cmsApi       = $cmsApi;
        $this->indexerApi   = $indexerApi;
        $this->documentApi  = $documentApi;
        $this->site         = $site;
        $this->cache        = $cache;
    }

    /**
     * Attaches event listeners
     */
    public function attachListeners()
    {
        parent::attachListeners();
        $eventManager   = $this->getEventManager();
        $eventManager->attach(ComponentEventInterface::EVENT_INIT, array($this, 'viewListenerSetChildren'));
    }

    /**
     * View listener - sets children
     */
    public function viewListenerSetChildren()
    {
        if ($this->cache) {
            $key        = $this->getCacheKeyHash();
            $success    = null;
            $children   = $this->cache->getItem($key, $success);
            if (!$success) {
                $children  = $this->getDocuments();
                $this->cache->setItem($key, $children);
            }

        } else {
            $children   = $this->getDocuments();
        }
        //Prepare view
        $this->getView()->children = $children;
    }

    /**
     * Returns cache key hash used to cache the overview documents
     * @return string
     * @throws \Vivo\CMS\UI\Exception\RuntimeException
     */
    protected function getCacheKeyHash()
    {
        /** @var $overviewModel \Vivo\CMS\Model\Content\Overview */
        $overviewModel  = $this->content;
        switch ($overviewModel->getOverviewType()) {
            case OverviewModel::TYPE_DYNAMIC:
                if (is_null($overviewModel->getOverviewPath())) {
                    //Overview path not specified, use the current requested doc
                    $overviewPath = $this->cmsEvent->getRequestedPath();
                } else {
                    //Overview path specified
                    $overviewPath = $overviewModel->getOverviewPath();
                }
                $keyParts   = array(
                    'site_name'         => $this->site->getName(),
                    'requested_path'    => $this->cmsEvent->getRequestedPath(),
                    'overview_path'     => $overviewPath,
                    'overview_criteria' => $overviewModel->getOverviewCriteria(),
                    'overview_sorting'  => $overviewModel->getOverviewSorting(),
                    'overview_limit'    => $overviewModel->getOverviewLimit(),
                );
                $hash   = $this->hashCacheKey(implode(',', $keyParts));
                break;
            case OverviewModel::TYPE_STATIC:
                $concat = $this->site->getName() . ',';
                foreach ($overviewModel->getOverviewItems() as $docPath) {
                    $concat .= $docPath;
                }
                $hash   = $this->hashCacheKey($concat);
                break;
            default:
                throw new RuntimeException(sprintf("%s: Unsupported overview type '%s'",
                    __METHOD__, $overviewModel->getOverviewType()));
                break;
        }
        return $hash;
    }

    /**
     * Hashes cache key
     * @param string $key
     * @return string
     */
    protected function hashCacheKey($key)
    {
        $hash   = hash('md4', $key);
        return $hash;
    }

    /**
     * Returns documents to list in overview.
     *
     * @throws Exception
     * @return \Vivo\CMS\Model\Document[]
     */
    public function getDocuments()
    {
        $documents = array();
        $type = $this->content->getOverviewType();
        if ($type == OverviewModel::TYPE_DYNAMIC) {
            if ($path = $this->content->getOverviewPath()) {
                $path = $this->cmsApi->getEntityAbsolutePath($path, $this->site);
            } else {
                $path = $this->document->getPath();
            }

            $query = $this->createQuery($path,$this->content->getOverviewCriteria());

            $params = array();
            if ($limit = $this->content->getOverviewLimit()) {
                $params['page_size'] = $limit;
            }
            if ($sort = $this->content->getOverviewSorting()) {
                $currentDoc    = $this->cmsApi->getSiteEntity($this->content->getOverviewPath(), $this->site);
                $parentSorting = $currentDoc->getSorting();
                if(strpos($sort, "parent") !== false && $parentSorting != null) {
                    $sort = $parentSorting;
                }
                if(strpos($sort, ":") !== false){
                    $propertyName = substr($sort, 0,  strpos($sort,':'));
                    $sortWay = substr($sort,strpos($sort,':')+1);
                } else {
                    $propertyName = $sort;
                    $sortWay = 'asc';
                }
                if($propertyName == 'random') {
                    //$params['sort'] = "\\random_" . mt_rand(1, 10000);
                    //@TODO VP-187 Implement random sorting
                    $params['sort'] = '\\title asc';
                } else {
                    $params['sort'] = '\\' . $propertyName . ' ' . $sortWay;
                }
            }

            $items = $this->indexerApi->getEntitiesByQuery($query, $params);
            foreach ($items as $item) { /* @var $item \Vivo\CMS\Model\Document */
                if ($this->documentApi->isPublished($item)) {
                    $documents[] = $item;
                }
            }
        } elseif ($type == OverviewModel::TYPE_STATIC) {
            $items  = $this->content->getOverviewItems();
            foreach ($items as $item) {
                try {
                    /* @var $item \Vivo\CMS\Model\Document */
                    $document = $this->cmsApi->getSiteEntity($item, $this->site);
                    if ($document instanceof Document
                        && $document->getAllowListingInOverview()
                        && $this->documentApi->isPublished($document))
                    {
                        $documents[] = $document;
                    }
                } catch (EntityNotFoundException $e) {
                    $events = new \Zend\EventManager\EventManager();
                    $events->trigger('log', $this,
                            array ('message' => $e->getMessage(), 'level' => \Zend\Log\Logger::WARN));
                }
            }
        } else {
            throw new Exception(sprintf('%s: Unsupported overview type `%s`.', __DIR__, $type));
        }
        return $documents;
    }

    /**
     * Assembles string query for indexer.
     *
     * Searches only for published documents.
     *
     * @param string $path Absolute path of document.
     * @param string $criteria Indexer query.
     * @return string
     */
    protected function createQuery($path, $criteria)
    {
        $query = '\path:"'. $path . '/*" ';
        $query.= ' AND \class:"Vivo\CMS\Model\Document"';
        $query.= ' AND \publishedContents:"*"';  // search only documents with published content
        $query.= ' AND \allowListingInOverview:"1"';
        if ($criteria) {
            $criteria   = $this->makePathsAbsolute($criteria);
            $query.= " AND ($criteria)";
        } else {
            $query.= ' AND NOT \path:"' . $path . '/*/*" '; //exclude sub-documents
        }
        return $query;
    }

    /**
     * Converts any relative path found in the string to absolute path
     * @param string $stringWithPaths
     * @return string
     */
    protected function makePathsAbsolute($stringWithPaths)
    {
        $re     = '/(\.|)(' . SymRefConvertorInterface::PATTERN_URL . ')(\*)?/';
        $cmsApi = $this->cmsApi;
        $site   = $this->site;
        $callback   = function(array $matches) use ($cmsApi, $site) {
            $url    = $matches[2];
            if (isset($matches[3])) {
                $wildcard   = true;
            } else {
                $wildcard   = false;
            }
            try {
                /** @var $doc Entity */
                $doc        = $cmsApi->getSiteEntity($url, $site);
                $absPath    = $doc->getPath();
                if ($wildcard) {
                    //This is a fix for URLs with wildcard e.g. /cs/about-us/*
                    //If the trailing wildcard was not matched and added, such relative URL would be converted to
                    // SiteName/ROOT/cs/about-us* (note the missing slash) and it would match also the /cs/about-us/
                    //document in the indexer which was not intended - only its children were requested
                    $absPath    .= '/*';
                }
            } catch (EntityNotFoundException $e) {
                $absPath    = $url;
            }
            return $absPath;
        };
        $converted  = preg_replace_callback($re, $callback, $stringWithPaths);
        return $converted;
    }
}
