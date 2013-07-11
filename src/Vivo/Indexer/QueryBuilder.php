<?php
namespace Vivo\Indexer;

use Vivo\Indexer\Query\QueryInterface;
use Vivo\Indexer\Query\BooleanAnd;
use Vivo\Indexer\Query\BooleanOr;
use Vivo\Indexer\Query\BooleanNot;
use Vivo\Indexer\Query\Range;
use Vivo\Indexer\Query\Term as TermQuery;
use Vivo\Indexer\Query\Wildcard;
use Vivo\Indexer\Term as IndexerTerm;
use Vivo\Indexer\Query\Parser\TokenInterface;

/**
 * QueryBuilder
 */
class QueryBuilder
{
    /**
     * Creates a query from the specified condition
     * @param string $cond
     * @param string|null $field
     * @param bool $neg
     * @throws Exception\InvalidArgumentException
     * @return QueryInterface
     */
    public function cond($cond, $field = null, $neg = false)
    {
        $cond       = trim($cond);
        $matches    = array();
        if (preg_match(TokenInterface::RE_RANGE_LITERAL, $cond, $matches) === 1) {
            //Range query
            if (is_null($field)) {
                throw new Exception\InvalidArgumentException(
                    sprintf('%s: Field name must be specified for range conditions', __METHOD__));
            }
            $query  = new Range($field, $matches[1], $matches[2]);
        } elseif (mb_strpos($cond, '*') !== false) {
            //Wildcard query
            $pattern    = new IndexerTerm($cond, $field);
            $query      = new Wildcard($pattern);
        } else {
            //Term query
            $term       = new IndexerTerm($cond, $field);
            $query      = new TermQuery($term);
        }
        if ($neg) {
            $query      = $this->notX($query);
        }
        return $query;
    }

    /**
     * Negates and returns a query
     * @param QueryInterface $query
     * @return BooleanNot
     */
    public function notX($query)
    {
        $query = new BooleanNot($query);
        return $query;
    }

    /**
     * Prepares parameters for AND or OR operation
     * @param QueryInterface|array $left
     * @param QueryInterface $right
     * @throws \Vivo\Indexer\Exception\InvalidArgumentException
     * @return array <\Vivo\Indexer\Query\QueryInterface>
     */
    private function prepareTwoX($left, QueryInterface $right = null)
    {
        if($left instanceof QueryInterface && $right instanceof QueryInterface) {
            $left = array($left, $right);
        }

        if(!is_array($left)) {
            throw new Exception\InvalidArgumentException(
            sprintf('%s: First argument must be instance of Vivo\Indexer\Query\QueryInterface or array', __METHOD__));
        }

        if(count($left) < 2) {
            throw new Exception\InvalidArgumentException(
                    sprintf('%s: Array must have more than 2 elements', __METHOD__));
        }

        foreach ($left as $key=>$condition) {
            if(!$condition instanceof QueryInterface) {
                throw new Exception\InvalidArgumentException(
                        sprintf('%s: Element at index %s is not instance of Vivo\Indexer\Query\QueryInterface; %s given',
                                __METHOD__, $key, gettype($condition)));
            }
        }

        return $left;
    }

    /**
     * Combines two queries into an AND query
     * @param QueryInterface|array $left
     * @param QueryInterface $right
     * @throws \Vivo\Indexer\Exception\InvalidArgumentException
     * @return BooleanAnd
     */
    public function andX($left, QueryInterface $right = null)
    {
        $left = $this->prepareTwoX($left, $right);
        $query = array_shift($left);
        foreach ($left as $condition) {
            $query = new BooleanAnd($query, $condition);
        }

        return $query;
    }

    /**
     * Combines two queries into an OR query
     * @param QueryInterface|array $left
     * @param QueryInterface $right
     * @throws \Vivo\Indexer\Exception\InvalidArgumentException
     * @return BooleanOr
     */
    public function orX($left, QueryInterface $right = null)
    {
        $left = $this->prepareTwoX($left, $right);
        $query = array_shift($left);
        foreach ($left as $condition) {
            $query = new BooleanOr($query, $condition);
        }

        return $query;
    }
}
