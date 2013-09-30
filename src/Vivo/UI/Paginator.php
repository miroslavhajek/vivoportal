<?php
namespace Vivo\UI;

use Vivo\UI\ComponentEventInterface;

/**
 * Paginator is a componet for pagination.
 */
class Paginator extends Component
{
    /**
     * Total items count
     * @var int
     */
    private $itemCount;

    /**
     * Items per page
     * @var int
     */
    private $itemsPerPage;

    /**
     * Number of page
     * @var int
     */
    private $page;

    /**
     * Page count
     * @var int
     */
    private $pageCount;

    /**
     * Page param name
     * @var string
     */
    private $paramName;

    /**
     * Http request
     * @var Zend\Http\Request
     */
    private $request;

    /**
     * Other parameters in URL
     * @var array
     */
    private $params = array();

    /**
     * Construct Paginator..
     * @param Zend\Http\Request $request
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->itemCount = 0;
        $this->itemsPerPage = 10;
        $this->page = 1;
    }

    /**
     * Property setter
     * @param string $name
     * @param string $value
     * @deprecated
     * @todo: delete
     */
    public function __set($name, $value)
    {
        method_exists($this, 'set'.ucfirst($name)) ?
            $this->{'set'.ucfirst($name)}($value) :
            parent::__set($name, $value);
    }

    /**
     * Property getter
     * @param string $name
     * @deprecated
     * @todo: delete
     */
    public function __get($name)
    {
        return method_exists($this, 'get'.ucfirst($name)) ?
            $this->{'get'.ucfirst($name)}() :
            parent::__get($name);
    }

    /**
     * Sets page parameter from URL
     */
    public function initSetPage()
    {
        if ($page = $this->request->getQuery($this->getParamName())) {
            $this->setPage($page);
        }
    }

    public function attachListeners()
    {
        parent::attachListeners();

        $this->getEventManager()->attach(ComponentEventInterface::EVENT_VIEW, array($this, 'viewListenerPaginatorView'));
    }

    /**
     * Prepares View properties
     */
    public function viewListenerPaginatorView()
    {
        if ($this->pageCount < 2) {
            $steps = array($this->page);
        } else {
            $arr = range(max($this->firstPage, $this->page - 3), min($this->lastPage, $this->page + 3));
            $count = 4;
            $quotient = ($this->pageCount - 1) / $count;

            for ($i = 0; $i <= $count; $i++) {
                $arr[] = round($quotient * $i) + $this->firstPage;
            }

            sort($arr);
            $steps = array_values(array_unique($arr));
        }

        $stepsWithQueryStrings = array();

        for ($i = 0; $i < count($steps); $i++) {
            $stepsWithQueryStrings[$steps[$i]] = $this->getQueryString($steps[$i]);
        }

        $view = $this->getView();
        $view->steps = $stepsWithQueryStrings;
        $view->isFirst = $this->isFirst();
        $view->page = $this->getPage();
        $view->isLast = $this->isLast();
        $view->nextPageQueryString = $this->getQueryString($this->getNextPage());
        $view->prevPageQueryString = $this->getQueryString($this->getPrevPage());
        $view->showPaginator = ($this->pageCount > 1);
    }

    /**
     * Returns param name
     * @return string
     */
    public function getParamName()
    {
        if (!$this->paramName) {
            $this->paramName = 'page'.dechex(crc32($this->getPath()));
        }

        return $this->paramName;
    }

    /**
     * Add param. From params will be generated the query string.
     * @param string $name Param name.
     * @param string $value Param value.
     */
    public function addParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     *
     * @param int $page Page number.
     * @param bool $htmlescape
     */
    public function getQueryString($page = null, $htmlescape = true)
    {
        $page = max(1, $page === null ? $this->page : intval($page));

        $data = $this->params;
        $data[$this->getParamName()] = $page;

        $query = http_build_query($data, null, ($htmlescape ? '&amp;' : '&'));
        $query = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query);

        return $query;
    }

    /**
     * First page.
     * @return bool
     */
    public function isFirst()
    {
        return $this->page == $this->firstPage;
    }

    /**
     * Last page.
     * @return bool
     */
    public function isLast()
    {
        return $this->page == $this->lastPage;
    }

    /**
     * Sets page counts.
     */
    protected function recalculate()
    {
        $this->pageCount = ceil($this->itemCount/$this->itemsPerPage);
    }

    /**
     * Sets item count
     * @param int $itemCount
     */
    public function setItemCount($itemCount)
    {
        $this->itemCount = max((int)$itemCount, 0);
        $this->recalculate();
    }

    /**
     * Sets items per page
     * @param int $itemsPerPage
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = max((int)$itemsPerPage, 1);
        $this->recalculate();
    }

    /**
     * sets number of page
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = max((int)$page, 1);
    }

    /**
     * Sets page param name
     * @param string $name
     */
    public function setParamName($name)
    {
        $this->paramName = $name;
    }

    /**
     * gets page number
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns page index
     * @return int
     */
    public function getPageIndex()
    {
        return $this->page - 1;
    }

    /**
     * Returns first page
     * @return int
     */
    public function getFirstPage()
    {
        return 1;
    }

    /**
     * Returns last page
     * @return int
     */
    public function getLastPage()
    {
        return $this->firstPage + $this->pageCount - 1;
    }

    /**
     * Returns next page
     * @return int
     */
    public function getNextPage()
    {
        return $this->page < $this->lastPage ? $this->page+1 : $this->page;
    }

    /**
     * Returns previous page
     * @return int
     */
    public function getPrevPage()
    {
        return $this->page > $this->firstPage ? $this->page-1 : $this->page;
    }

    /**
     * Returns page count
     * @return int
     */
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * Returns item count
     * @return int
     */
    public function getItemCount()
    {
        return $this->itemCount;
    }

    /**
     * Returns items per page
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * Retuns offset
     * @return int
     */
    public function getOffset()
    {
        return ($this->page-1)*$this->itemsPerPage;
    }

    /**
     * Returns limit
     * @return int
     */
    public function getLength()
    {
        return min($this->itemsPerPage, $this->itemCount-$this->offset);
    }
}