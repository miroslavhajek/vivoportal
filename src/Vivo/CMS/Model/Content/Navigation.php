<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;
use Vivo\CMS\Exception;

/**
 * Class Navigation
 * @package Vivo\CMS\Model\Content
 */
class Navigation extends Model\Content
{
    /**
     * Navigation type ORIGIN
     * Navigation tree will be calculated from the document graph
     * The starting point for calculation is the specified origin path ($this->origin)
     * or the current document if $this->root is not set
     */
    const TYPE_ORIGIN       = 'type_origin';

    /**
     * Navigation type ENUM
     * Explicitly named documents will be included in the navigation container
     */
    const TYPE_ENUM         = 'type_enum';

    /**
     * Array of supported navigation types
     * @var array
     */
    protected $supportedTypes   = array(
        self::TYPE_ORIGIN,
        self::TYPE_ENUM,
    );

    /**
     * Selected navigation type
     * @var string
     */
    protected $type;

    /**
     * Path of an entity which is the origin for the navigation tree calculation
     * If null, the current document is assumed as origin
     * @var string
     */
    protected $origin;

    /**
     * Where to start building the navigation?
     * Zero     = Current document or origin
     * Positive = This absolute level (levels start at 1)
     * Negative = This number of levels up from the origin
     * @var integer
     */
    protected $startLevel   = 0;

    /**
     * Number of levels scanned from root
     * Null means unlimited
     * @var int
     */
    protected $levels;

    /**
     * Should the root document be included in the navigation container?
     * @var bool
     */
    protected $includeRoot  = false;

    /**
     * Only a single branch of documents should be included in the navigation tree
     * Used for breadcrumbs
     * @var bool
     */
    protected $branchOnly   = false;

    /**
     * Number of documents listed in the navigation per level
     * Null means unlimited
     * @var int
     */
    protected $limit;

    /**
     * Determines way of sorting navigation documents
     * @var string
     */
    protected $navigationSorting = 'title:asc';

    /**
     * Array of explicitly enumerated documents to include in the navigation
     * Every document is represented as an array element 'doc_path' and optional element 'children'
     * The 'children' element may contain similar arrays
     * array(
     *      array(
     *          'doc_path'  => '/path/to/doc1/',
     *          'children'  => array(
     *              ...
     *          ),
     *      ),...
     * )
     * @var array
     */
    protected $enumeratedDocs   = array();

    /**
     * Sets if root should be included in the navigation
     * @param boolean $includeRoot
     */
    public function setIncludeRoot($includeRoot)
    {
        $this->includeRoot = (bool) $includeRoot;
    }

    /**
     * Returns if root should be included in navigation
     * @return bool
     */
    public function includeRoot()
    {
        return $this->includeRoot;
    }

    /**
     * Alias for includeRoot()
     * @return bool
     */
    public function getIncludeRoot()
    {
        return $this->includeRoot();
    }

    /**
     * Sets number of levels from root included in navigation
     * @param int $levels
     */
    public function setLevels($levels)
    {
        if ($levels === '') {
            $levels = null;
        } else {
            $levels = (int)$levels;
        }
        $this->levels = $levels;
    }

    /**
     * Returns number of levels from root included in navigation
     * @return int
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * Sets origin path where navigation calculation starts
     * @param string $root
     */
    public function setOrigin($root = null)
    {
        if ($root == '') {
            $root = null;
        }
        $this->origin = $root;
    }

    /**
     * Returns origin path where the navigation calculation starts
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Sets navigation type
     * @param string $type
     * @throws \Vivo\CMS\Exception\InvalidArgumentException
     */
    public function setType($type)
    {
        if (!in_array($type, $this->supportedTypes)) {
            throw new Exception\InvalidArgumentException(
                sprintf("%s: Unsupported navigation type '%s'", __METHOD__, $type));
        }
        $this->type = $type;
    }

    /**
     * Returns navigation type
     * @return string One of self::TYPE_... constants
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the enumerated documents
     * @param array $enumeratedDocs
     */
    public function setEnumeratedDocs(array $enumeratedDocs)
    {
        $this->enumeratedDocs = $enumeratedDocs;
    }

    /**
     * Returns the enumerated documents
     * @return array
     */
    public function getEnumeratedDocs()
    {
        return $this->enumeratedDocs;
    }

    /**
     * Sets the start level
     * @param int $startLevel
     */
    public function setStartLevel($startLevel)
    {
        $startLevel         = (int) $startLevel;
        $this->startLevel   = $startLevel;
    }

    /**
     * Returns the start level
     * @return int
     */
    public function getStartLevel()
    {
        return $this->startLevel;
    }

    /**
     * Sets if only a single branch of documents should be included in the navigation
     * @param boolean $branchOnly
     */
    public function setBranchOnly($branchOnly)
    {
        $this->branchOnly = (bool) $branchOnly;
    }

    /**
     * Returns if only a single branch of documents should be included in the navigation
     * @return boolean
     */
    public function getBranchOnly()
    {
        return $this->branchOnly;
    }

    /**
     * Sets way of sorting navigation documents
     * @param string $sorting
     */
    public function setNavigationSorting($sorting)
    {
        $this->navigationSorting = (string) $sorting;
    }

    /**
     * Returns way of sorting navigation documents
     * @return string
     */
    public function getNavigationSorting()
    {
        return $this->navigationSorting;
    }

    /**
     * Sets number of documents listed in the navigation per level
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = ((int) $limit != 0) ? (int) $limit : null;
    }

    /**
     * Returns number of documents listed in the navigation per level
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
}
