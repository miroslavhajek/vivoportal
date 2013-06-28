<?php

namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * VIVO model represents search in documents on front-end.
 */
class Search extends Model\Content implements Model\SymRefDataExchangeInterface
{    
    
    /**
     * @var int Page size
     */
    protected $pageSize;

    /**
     * Results count options
     * @var array
     */
    static $PAGE_SIZE_OPTIONS = array(4 => 4, 10 => 10, 20 => 20, 40 => 40, 60 => 60);
    
    /**
     * Operator
     * @var string
     */
    protected $operator;
    
    /**
     * Operator options
     */
    const EXACT_OP = 'exact_op';
    const AND_OP = 'and_op';
    const OR_OP = 'or_op';

    /**
     * Wildcards
     * @var string Condition for parts of words.
     */
    protected $wildcards;
    
    /** 
     * Wildcards options
     */
    const AS_IS = 'as_is';
    const START_ASTERISK = 'start_asterisk';
    const END_ASTERISK = 'end_asterisk';
    const START_END_ASTERISK = 'start_end_asterisk';

    /**
     * @var bool Resource link in the results.
     */
    protected $resourceLink;
    
    /**
    * Limit the maximum number of results returned Solr. Too large a value may cause problems with power / memory
    * @var integer
    * @see Vivo\CMS\UI\Content\Search::doSearch()
    */
    static $SEARCH_LIMIT = 100;

    /**
     * Setting default values.
     * @param string $path Entity path.
     */
    public function __construct($path = null)
    {
        parent::__construct($path);
    }
    
    /**
     * Returns max page size
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }
    
    /**
     * Sets max page size
     * @param int $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = (int) $pageSize;
    }
    
    /**
     * Returns search operator
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }
    
    /**
     * Returns operator options
     * @return string
     */
    public function getOperatorOptions()
    {
        return array(
            'exact_op' => 'exact_op',
            'and_op' => 'and_op',
            'or_op' => 'or_op'
        );
    }
    
    /**
     * Sets search operator
     * @param string $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }
    
    /**
     * Returns search wildcards
     * @return string
     */
    public function getWildcards()
    {
        return $this->wildcards;
    }
    
    /**
     * Returns wildcards options
     * @return string
     */
    public function getWildcardsOptions()
    {
        return array(
            'as_is' => 'as_is',
            'start_asterisk' => 'start_asterisk',
            'end_asterisk' => 'end_asterisk',
            'start_end_asterisk' => 'start_end_asterisk',
        );
    }
    
    /**
     * Sets search wildcards
     * @param string $wildcards
     */
    public function setWildcards($wildcards)
    {
        $this->wildcards = $wildcards;
    }
    
    /**
     * Returns search resource link
     * @return int
     */
    public function getResourceLink()
    {
        return $this->resourceLink;
    }
    
    /**
     * Sets search resource link
     * @param string $resourceLink
     */
    public function setResourceLink($resourceLink)
    {
        $this->resourceLink = $resourceLink;
    }

    /**
     * Exchange internal values containing symbolic refs / URLs from provided array
     * @param  array $data
     * @return void
     */
    public function exchangeArraySymRef(array $data)
    {

    }

    /**
     * Return an array representation of the object's properties containing symbolic refs / URLs
     * @return array
     */
    public function getArrayCopySymRef()
    {
        $data = array();
        return $data;
    }
}
