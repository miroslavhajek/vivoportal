<?php
namespace Vivo\Backend\UI\Explorer;

use Vivo\CMS\Api;
use Vivo\CMS\Model\Site;
use Vivo\CMS\Model\Document;
use Vivo\CMS\Util;
use Vivo\Repository\Exception\EntityNotFoundException;
use Vivo\Service\Initializer\TranslatorAwareInterface;
use Vivo\Indexer\IndexerInterface;
use Vivo\Indexer\QueryBuilder;
use Vivo\UI\Alert;
use Vivo\UI\Component;
use Vivo\Util\UrlHelper;
use Vivo\Util\RedirectEvent;
use Zend\EventManager\Event;
use Zend\I18n\Translator\Translator;
use Zend\View\Model\JsonModel;
use Zend\EventManager\EventManager;

class Finder extends Component implements TranslatorAwareInterface
{
    /**
     * @var \Vivo\CMS\Api\CMS
     */
    protected $cmsApi;

    /**
     * @var \Vivo\CMS\Api\Document
     */
    protected $documentApi;

    /**
     * Indexer API
     * @var \Vivo\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * @var \Vivo\CMS\Model\Site
     */
    protected $site;

    /**
     * @var \Vivo\Util\UrlHelper
     */
    protected $urlHelper;

    /**
     * @var \Vivo\CMS\Util\DocumentUrlHelper
     */
    protected $documentUrlHelper;

    /**
     * @var \Vivo\CMS\Util\IconUrlHelper
     */
    protected $iconUrlHelper;

    /**
     * @var \Vivo\Backend\UI\Explorer\ExplorerInterface
     */
    protected $explorer;

    /**
     * @var \Vivo\UI\Alert
     */
    protected $alert;

    /**
     * @var \Vivo\CMS\Model\Entity
     */
    protected $entity;

    /**
     * @var \Zend\I18n\Translator\Translator
     */
    protected $translator;

    /**
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param \Vivo\CMS\Api\Document $documentApi
     * @param \Vivo\Indexer\IndexerInterface $indexer
     * @param \Vivo\Util\UrlHelper $urlHelper
     * @param \Vivo\CMS\Util\DocumentUrlHelper $documentUrlHelper
     * @param \Vivo\CMS\Util\IconUrlHelper $documentUrlHelper
     * @param \Vivo\CMS\Model\Site $site
     */
    public function __construct(Api\CMS $cmsApi, Api\Document $documentApi, IndexerInterface $indexer,
            UrlHelper $urlHelper, Util\DocumentUrlHelper $documentUrlHelper, Util\IconUrlHelper $iconUrlHelper,
            Site $site)
    {
        $this->cmsApi = $cmsApi;
        $this->documentApi = $documentApi;
        $this->indexer = $indexer;
        $this->urlHelper = $urlHelper;
        $this->documentUrlHelper = $documentUrlHelper;
        $this->iconUrlHelper = $iconUrlHelper;
        $this->site = $site;
    }

    public function init()
    {
        $this->entity = $this->explorer->getEntity();
    }

    public function setExplorer(ExplorerInterface $explorer)
    {
        $this->explorer = $explorer;
    }

    /**
     * Return current entity.
     * @todo
     * @deprecated Test and remove.
     * @return \Vivo\CMS\Model\Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Sets Alert component.
     * @param Alert $alert
     */
    public function setAlert(Alert $alert)
    {
        $this->alert = $alert;
    }

    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns informations about parents.
     * @param string $url
     * @return array
     */
    private function getParentsByUrl($url)
    {
        $path = explode('/', trim($url, '/'));
        $realPaths = array('/');
        $titles = array();
        $i = 0;
        foreach ($path as $part) {
            $path = isset($realPaths[$i - 1]) ? $realPaths[$i - 1] : '';
            $path.= '/'.$part;

            $realPaths[$i] = $path;
            $i++;
        }

        array_unshift($realPaths, '/');

        foreach($realPaths as $realPath) {
            $entity = $this->cmsApi->getSiteEntity($realPath, $this->site);
            $actionUrl = $this->urlHelper->fromRoute('backend/explorer', array('path'=>$entity->getUuid()));

            $titles[] = array(
                'title' => $entity->getTitle(),
                'actionUrl' => $actionUrl,
            );
        }

        return $titles;
    }

    /**
     * JS support method for rewriting pats titles of documents.
     * @example /document/sub-document/ -> /Document name/Green subdocument/
     * @param string $path
     * @return \Zend\View\Model\JsonModel
     */
    public function getTitles($url = '/')
    {
        $data = array();
        $titles = $this->getParentsByUrl($url);

        foreach ($titles as $title) {
            $data[] = $title['title'];
        }

        $view = new JsonModel();
        $view->data = $data;

        return $view;
    }

    /**
     * Returns informations about subdocuments (folders) by URL
     * @param string $path
     * @return \Zend\View\Model\JsonModel
     */
    public function getSubEntities($path)
    {
        $info = array();
//      if (substr($url, 0, 1) == '/') { //TODO: warum? jestli nic, smazat...
            $document = $this->cmsApi->getSiteEntity($path, $this->site);

            /* @var $child \Vivo\CMS\Model\Folder */
            foreach ($this->documentApi->getChildDocuments($document) as $child) {
                $folder = !($child instanceof Document);
                $published = $folder ? false : $this->documentApi->isPublished($child);
                $actionUrl = $this->urlHelper->fromRoute('backend/explorer', array('path'=>$child->getUuid()));
                $iconUrl = $this->iconUrlHelper->getByFolder($child);

                $info[] = array(
                    'title' => $child->getTitle(),
                    'path' => $child->getPath(),
                    'folder' => intval($folder),
                    'published' => intval($published),
                    'icon' => $iconUrl,
                    'actionUrl' => $actionUrl,
                );
            }
//      }

        $view = new JsonModel();
        $view->data = $info;

        return $view;
    }

    /**
     * Ajax action for search
     * @param string $input
     * @return \Zend\View\Model\ModelInterface
     */
    public function renderSearchPulldown($input)
    {
        $words = explode(' ', $input);

        if(!$words) {
            return null;
        }

        $qb = new QueryBuilder();
        $documents = array();
        $fieldCons = array();
        foreach (array('\title', '\path', '\uuid') as $field) {
            $wordCons = array();
            foreach ($words as $word) {
                $wordCons[] = $qb->cond("*$word*", $field);
            }

            if(count($wordCons) > 1) {
                $fieldCons[] = $qb->andX($wordCons);
            }
            else {
                $fieldCons[] = $wordCons[0];
            }
        }

        $condition = $qb->andX($qb->cond($this->site->getPath().'/*', '\path'), $qb->orX($fieldCons));
        $condition = $qb->andX($qb->cond('Vivo\CMS\Model\Document', '\class'), $condition);
        $hits      = $this->indexer->find($condition)->getHits();

        foreach ($hits as $hit) {
            $path     = $hit->getDocument()->getFieldValue('\path');
            $document = $this->documentApi->getEntity($path);
            $published = ($document instanceof Document) ? $this->documentApi->isPublished($document) : false;

            $documents[] = array(
                'document' => $document,
                'published' => $published,
            );
        }

        $view = parent::view();
        $view->setTemplate(__CLASS__.':SearchPulldown');
        $view->data = $documents;
        $view->documentsCount = count($documents);

        return $view;
    }

    /**
     * Opens entity editor by URL / UUID
     * @param string $url
     */
    public function redirectToUrl($url)
    {
        try {
            $document = $this->cmsApi->getSiteEntity($url, $this->site);
            $url = $this->urlHelper->fromRoute('backend/explorer', array('path' => $document->getUuid()));
            $events = new EventManager();
            $events->trigger(new RedirectEvent($url));
        }
        catch(\Vivo\CMS\Exception\InvalidArgumentException $e) {
            $this->alert->addMessage('Neplatný formát URL', Alert::TYPE_WARNING);
        }
        catch(\Vivo\Repository\Exception\EntityNotFoundException $e) {
            $this->alert->addMessage('Entita na zadané URL neexistuje', Alert::TYPE_WARNING);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Vivo\UI\Component::view()
     */
    public function view()
    {
        $view = parent::view();
        $view->siteTitle = $this->site->getTitle();
        $view->entity    = $this->entity;
        $view->entities  = $this->getParentsByUrl($this->documentUrlHelper->getDocumentUrl($this->entity));

        return $view;
    }
}
