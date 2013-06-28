<?php
namespace Vivo\CMS\UI\Content;

use Vivo\CMS\Api\CMS;
use Vivo\CMS\Api\Document as DocumentApi;
use Vivo\SiteManager\Event\SiteEvent;
use Vivo\UI\Paginator;
use Vivo\Indexer\QueryBuilder;
use Vivo\CMS\UI\AbstractForm;
use Vivo\CMS\Model\Content\Search as SearchModel;
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
    * @var int
    */
   public $max_score = 1;
   
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
    protected $documentApi;

    /**
     * @var SiteEvent
     */
    private $siteEvent;
    
    /**
     * Paginator
     * @var $paginator
     */
    protected $paginator;
    
    /**
     * Search result
     * @var array
     */
    protected $result;   
    
    /**
     * Searched result count
     * @var int
     */
    protected $numRows; 

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
    }
    
    public function init()
    {               
        parent::init();
        $this->addComponent($this->paginator, 'paginator');
        $this->paginator->init();
        $this->paginator->initSetPage();
        
        $this->paginator->setItemsPerPage($this->content->getPageSize());
        
        $this->view->max_score = $this->max_score;
        $this->view->paginator = $this->paginator;
        
        //Add params to paginator
        if($params = $this->request->getQuery()->toArray()) {
            $this->paginator->addParam('words', $params['words']);
            $this->paginator->addParam('operator', $params['operator']);
            $this->paginator->addParam('wildcards', $params['wildcards']);
            $this->paginator->addParam('act', $this->getPath('getPage'));        
        }
    }
    
    public function getPage() {
        $this->doSearch($this->request->getQuery()->toArray());
    }
    
    public function search() {
        $form = $this->getForm();
        if ($form->isValid()) {
            $this->doSearch($form->getData());
        }
    }
    
    public function doSearch(array $data) {
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

        $words  = $this->addFieldConstraintToQueryParts(explode(' ', $data['words']));

        $query = '';
        $currentQuery = '';
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
                    $currentQuery = $qb->cond(join(' ', $words));
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
                
        $currentQuery = $qb->andX($currentQuery, $qb->cond($this->siteEvent->getSite()->getPath() . '/*','\path'));
        
        $params = array();
        
        // Limit and offset of paginator are not used because results are filtered next and count does not match.
        $params['page_size'] = SearchModel::$SEARCH_LIMIT;
        $result = $this->indexer->find($currentQuery, $params);
        
        $this->numRows = $result->getTotalHitCount();

        foreach ($result->getHits() as $hit) {
            $entityPath = $hit->getDocument()->getField('\path')->getValue();
            try {
                $entity = $this->cmsApi->getSiteEntity($entityPath, $this->siteEvent->getSite());
            } catch (EntityNotFoundException $e) {
                $this->numRows -= 1;
                continue;
            }
            if ($entity instanceof \Vivo\CMS\Model\Document) {
                if (!$this->documentApi->isPublished($entity)) {
                    $this->numRows -= 1;
                    continue;
                }
            } elseif ($entity instanceof \Vivo\CMS\Model\Content) {
                $entity = $this->cmsApi->getParent($this->cmsApi->getParent($entity));
                $entityPath = $entity->getPath();
                if (!$this->documentApi->isPublished($entity)) {
                    $this->numRows -= 1;
                    continue;
                }
            }
            if ($entity instanceof \Vivo\CMS\Model\Document && $entity->getSearchable() && !isset($documentList[$entityPath])) {
                $documentList[$entityPath]['title'] = $entity->getTitle();
                $documentList[$entityPath]['description'] = $entity->getDescription();
                $documentList[$entityPath]['url'] = $this->cmsApi->getEntityRelPath($entity);
                $documentList[$entityPath]['score'] = $hit->getScore();
            } else {
                $this->numRows -= 1;
            }
        }
        
        $this->paginator->setItemCount($this->numRows);
        //Choose pages from result
        $resultPages = array_chunk($documentList, $this->content->getPageSize());
        $this->result = $resultPages[$this->paginator->getPage() - 1];
        $this->view->result = $this->result;
    }
    
    /**
     * @param array $query_parts
     * @return array
     */
    protected function addFieldConstraintToQueryParts(array $query_parts) 
    {
        $new_array = array();
        foreach ($query_parts as $part) {
            $new_array[] = mb_strtolower($part, mb_detect_encoding($part));
        }
        return $new_array;
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
                'value' => 'Search',
            ),
        ));    

        return $form;        
    }

}
