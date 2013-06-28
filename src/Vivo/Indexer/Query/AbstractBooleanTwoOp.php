<?php
namespace Vivo\Indexer\Query;

/**
 * AbstractBooleanTwoOp
 * Abstract boolean two operator query
 */
abstract class AbstractBooleanTwoOp implements BooleanTwoOpInterface
{
    /**
     * Left query
     * @var QueryInterface
     */
    protected $left;

    /**
     * Right query
     * @var QueryInterface
     */
    protected $right;

    /**
     * Constructor
     * @param QueryInterface $queryLeft
     * @param QueryInterface $queryRight
     */
    public function __construct(QueryInterface $queryLeft, QueryInterface $queryRight)
    {
        $this->left     = $queryLeft;
        $this->right    = $queryRight;
    }

    /**
     * Returns the query on the left side of the AND operand
     * @return QueryInterface
     */
    public function getQueryLeft()
    {
        return $this->left;
    }

    /**
     * Returns the query on the right side of the AND operand
     * @return QueryInterface
     */
    public function getQueryRight()
    {
        return $this->right;
    }
    
    /**
     * Sets the query on the left side of the AND operand
     * @param QueryInterface $left
     */
    public function setQueryLeft(QueryInterface $left)
    {
        $this->left = $left;
    }

    /**
     * Sets the query on the right side of the AND operand
     * @param QueryInterface $right
     */
    public function setQueryRight(QueryInterface $right)
    {
        $this->right = $right;
    }
}
