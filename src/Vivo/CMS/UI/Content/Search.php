<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\Api\CMS;
use Vivo\CMS\Api\Document as DocumentApi;
use Vivo\CMS\Model;
use Vivo\CMS\UI\AbstractForm;
use Vivo\CMS\Model\Content\Search as SearchModel;
use Vivo\UI\Paginator;
use Vivo\UI\ComponentEventInterface;
use Vivo\SiteManager\Event\SiteEvent;
use Vivo\Indexer\QueryBuilder;
use Vivo\Form\Form;
use Vivo\Indexer\Query\QueryInterface;
use Vivo\Repository\Exception\EntityNotFoundException;

/**
 * Serach UI component
 *
 * Search for documents.
 */
class Search extends AbstractForm
{
    /**
     * @var \Vivo\CMS\Api\CMS
     */
    private $cmsApi;

    /**
     * @var \Vivo\Indexer\IndexerInterface
     */
    private $indexer;

    /**
     * Document API
     * @var DocumentApi
     */
    private $documentApi;

    /**
     * @var SiteEvent
     */
    private $siteEvent;

    /**
     * Paginator
     * @var $paginator
     */
    private $paginator;

    /**
     * Search result
     * @var array
     */
    private $result = array();

    /**
     * Searched result count
     * @var int
     */
    private $numRows = 0;

    /**
     * Constructor
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param \Vivo\CMS\Api\Document $documentApi
     * @param \Vivo\SiteManager\Event\SiteEvent $siteEvent
     * @param \Zend\Cache\Storage\StorageInterface $cache
     */
    public function __construct(
            CMS $cmsApi,
            \Vivo\Indexer\IndexerInterface $indexer,
            DocumentApi $documentApi,
            SiteEvent $siteEvent,
            Paginator $paginator)
    {
        $this->cmsApi      = $cmsApi;
        $this->indexer     = $indexer;
        $this->documentApi = $documentApi;
        $this->siteEvent   = $siteEvent;
        $this->paginator   = $paginator;
        $this->autoAddCsrf = false;
    }

    public function attachListeners()
    {
        parent::attachListeners();

        $eventManager = $this->getEventManager();
        $eventManager->attach(ComponentEventInterface::EVENT_INIT, array($this, 'initListenerSearchInit'));
        $eventManager->attach(ComponentEventInterface::EVENT_VIEW, array($this, 'viewListenerSearchView'));
    }

    public function initListenerSearchInit()
    {
        $this->addComponent($this->paginator, 'paginator');
        $this->paginator->init();
        $this->paginator->initSetPage();

        $this->paginator->setItemsPerPage($this->content->getPageSize());

        //Add params to paginator
        if($params = $this->request->getQuery()->toArray()) {
            $this->paginator->addParam('words', $params['words']);
            $this->paginator->addParam('operator', $params['operator']);
            $this->paginator->addParam('wildcards', $params['wildcards']);
            $this->paginator->addParam('act', $this->getPath('getPage'));
        }
    }

    public function viewListenerSearchView()
    {
        $view = $this->getView();
        $view->result = $this->result;
        $view->paginator = $this->paginator;
    }

    public function getPage()
    {
        $this->doSearch($this->request->getQuery()->toArray());
    }

    public function search()
    {
        $form = $this->getForm();
        if ($form->isValid()) {
            $this->doSearch($form->getData());
        }
    }

    /**
     * TODO: move to Vivo\CMS\Api\Content\Search
     * @param array $data
     */
    protected function doSearch(array $data)
    {
        $wordsString = trim($data['words']);
        if ($wordsString != '') {
            $qb = new QueryBuilder();
            $documentList = array();

            if($data['operator']) {
                $operator = $data['operator'];
            } else {
                $operator = $this->content->getOperator();
            }
            if($data['wildcards']) {
                $wildcards = $data['wildcards'];
            } else {
                $wildcards = $this->content->getWildcards();
            }

            $words  = $this->addFieldConstraintToQueryParts(explode(' ', $wordsString));
            $query = null;
            $currentQuery = null;
            foreach ($words as $key => $word) {
                if($operator != 'exact_op') {
                    if($wildcards == 'start_asterisk') {
                        $word = '*' . $word;
                    } elseif($wildcards == 'end_asterisk') {
                        $word = $word . '*';
                    } elseif($wildcards == 'start_end_asterisk') {
                        $word = '*' . $word . '*';
                    }
                }
                $query = $qb->cond($word);
                if($currentQuery instanceof QueryInterface){
                    if($operator == 'exact_op') {
                        $currentQuery = $qb->cond($wordsString);
                        break;
                    } elseif($operator == 'and_op') {
                        $currentQuery = $qb->andX($currentQuery, $query);
                    } elseif($operator == 'or_op') {
                        $currentQuery = $qb->orX($currentQuery, $query);
                    }
                } else {
                    $currentQuery = $query;
                }
            }

            $currentQuery = $qb->andX($currentQuery,
                                      $qb->cond($this->siteEvent->getSite()->getPath() . '/ROOT/*', '\path'));

            $result = $this->indexer->find($currentQuery, array('page_size' => 100));

            $this->numRows = 0;

            foreach ($result->getHits() as $hit) {
                $entityPath = $hit->getDocument()->getField('\path')->getValue();
                try {
                    $entity = $this->cmsApi->getSiteEntity($entityPath, $this->siteEvent->getSite());
                } catch (EntityNotFoundException $e) {
                    continue;
                }

                if ($entity instanceof Model\Content) {
                    $document = $this->documentApi->getContentDocument($entity);
                    if ($document instanceof Model\Document) {
                        $entity = $document;
                        $entityPath = $document->getPath();
                    }
                    else {
                        continue;
                    }
                }

                if ($entity instanceof Model\Document
                && !isset($documentList[$entityPath])
                && $entity->getSearchable()
                && $this->documentApi->isPublished($entity)) {
                    $documentList[$entityPath]['document'] = $entity;
                    $documentList[$entityPath]['score'] = $hit->getScore();

                    $this->numRows++;
                }
            }

            $this->paginator->setItemCount($this->numRows);
            //Choose pages from result
            $resultPages = array_chunk($documentList, $this->content->getPageSize());
            $this->result = $resultPages[$this->paginator->getPage() - 1];
        }
    }

    /**
     * @param array $queryParts
     * @return array
     */
    protected function addFieldConstraintToQueryParts(array $queryParts)
    {
        $array = array();
        foreach ($queryParts as $part) {
            $array[] = mb_strtolower($part, mb_detect_encoding($part));
        }
        return $array;
    }

    protected function doGetForm()
    {
        $form = new Form('Searchform');
        $form->setAttribute('method', 'get');
        $form->add(array(
            'name' => 'act',
            'attributes' => array(
                'type' => 'hidden',
                'value' => $this->getPath('search'),
            ),
        ));
        $form->add(array(
            'name' => 'words',
            'type' => 'Vivo\Form\Element\Text',
            'options' => array('label' => 'Search term'),
        ));
        $form->add(array(
            'name' => 'operator',
            'type' => 'Vivo\Form\Element\Select',
            'options' => array('label' => 'Operator'),
            'attributes' => array(
                'options' => $this->content->getOperatorOptions(),
                'value' => $this->content->getOperator()
            )
        ));
        $form->add(array(
            'name' => 'wildcards',
            'type' => 'Vivo\Form\Element\Select',
            'options' => array(
                'label' => 'Wildcards',
            ),
            'attributes' => array(
                'options' => $this->content->getWildcardsOptions(),
                'value' => $this->content->getWildcards()
            ),

        ));
        $form->add(array(
            'name' => 'submit',
            'type' => 'Vivo\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Search', //FIXME: Neodesilat slovo Search
            ),
        ));

        return $form;
    }
}
