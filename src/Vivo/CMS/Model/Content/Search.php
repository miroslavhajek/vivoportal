<?php

namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * VIVO model represents search in documents on front-end.
 */
class Search extends Model\Content
{

    /**
     * @var int Page size
     */
    protected $pageSize;

    /**
     * Results count options
     * @var array
     */
    protected $pagesizeOptions = array(4 => 4, 10 => 10, 20 => 20, 40 => 40, 60 => 60);

    /**
     * Operator
     * @var string
     */
    protected $operator;

    /**
     * Operator options
     */
    protected $operatorOptions = array(
        'exact_op' => 'exact_op',
        'and_op' => 'and_op',
        'or_op' => 'or_op'
    );

    /**
     * Wildcards
     * @var string Condition for parts of words.
     */
    protected $wildcards;

    /**
     * Wildcards options
     */
    protected $wildcardsOptions = array(
        'as_is' => 'as_is',
        'start_asterisk' => 'start_asterisk',
        'end_asterisk' => 'end_asterisk',
        'start_end_asterisk' => 'start_end_asterisk'
    );

    /**
     * @var bool Resource link in the results.
     */
    protected $resourceLink;

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
     * Returns page size options
     * @return array
     */
    public function getPagesizeOptions()
    {
        return $this->pagesizeOptions;
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
     * Sets search operator
     * @param string $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * Returns operator options
     * @return array
     */
    public function getOperatorOptions()
    {
        return $this->operatorOptions;
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
     * Sets search wildcards
     * @param string $wildcards
     */
    public function setWildcards($wildcards)
    {
        $this->wildcards = $wildcards;
    }

    /**
     * Returns wildcards options
     * @return array
     */
    public function getWildcardsOptions()
    {
        return $this->wildcardsOptions;
    }

    /**
     * Returns search resource link
     * @return string
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
}
