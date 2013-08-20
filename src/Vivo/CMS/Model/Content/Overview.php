<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * VIVO model represents overview documents by path and other criteria on front-end.
 */
class Overview extends Model\Content
{

    const TYPE_DYNAMIC = 'DYNAMIC';
    const TYPE_STATIC = 'STATIC';

    /**
     * Overview type.
     *
     * @var string see TYPE_DYNAMIC and TYPE_STATIC constants
     */
    protected $overviewType;

    /**
     * Path to a document, which sub-documents of it should be displayed in
     * the overview. If a overview path is not set, it shows sub-documents
     * of the current document, which overview is the content of that document.
     * @var string
     * @example /en/news/archive/
     */
    protected $overviewPath;

    /**
     * @var string Fulltext criteria.
     */
    protected $overviewCriteria;

    /**
     * @var string Documents sorting.
     * @see Model\Folder::$sorting
     */
    protected $overviewSorting = 'title:asc';

    /**
     * @var int A number represent documents count in overview.
     */
    protected $overviewLimit;

    /**
     * @var array items for static overview.
     */
    protected $overviewItems = array();

    /**
     * Sets overview type
     *
     * @param string $type Overview type
     **/
    public function setOverviewType($type)
    {
        $this->overviewType = $type;
    }

    /**
     * Returns overview path.
     * @return string
     */
    public function getOverviewPath()
    {
        return $this->overviewPath;
    }

    /**
     * Returns array of overview items (for static overview).
     * @return array
     */
    public function getOverviewItems()
    {
        return $this->overviewItems;
    }

    /**
     * Returns overview type (STATIC|DYNAMIC).
     * @return string
     */
    public function getOverviewType()
    {
        return $this->overviewType;
    }

    /**
     * @return integer
     */
    public function getOverviewLimit()
    {
        return $this->overviewLimit;
    }

    /**
     * @param integer $limit
     */
    public function setOverviewLimit($limit)
    {
        $this->overviewLimit = $limit;
    }

    /**
     * @return string
     */
    public function getOverviewSorting()
    {
        return $this->overviewSorting;
    }

    /**
     * @param string $sorting
     */
    public function setOverviewSorting($sorting)
    {
        $this->overviewSorting = $sorting;
    }

    /**
     * @return string
     */
    public function getOverviewCriteria()
    {
        return $this->overviewCriteria;
    }

    /**
     * @param string $criteria
     */
    public function setOverviewCriteria($criteria)
    {
        $this->overviewCriteria = $criteria;
    }

    /**
     * @param string $overviewPath
     */
    public function setOverviewPath($overviewPath)
    {
        $this->overviewPath = $overviewPath;
    }

    /**
     * @param array $overviewItems
     */
    public function setOverviewItems(array $overviewItems)
    {
        $this->overviewItems = $overviewItems;
    }
}
