<?php
namespace Vivo\Filter;

use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception\RuntimeException;

class KeyValueStringToArray extends AbstractFilter
{
    /**
     * (non-PHPdoc)
     * @see \Zend\Filter\FilterInterface::filter()
     * @throws \Zend\Filter\Exception\RuntimeException
     */
    public function filter($value)
    {
        $array = parse_ini_string($value);

        if($array === false) {
            throw new RuntimeException(sprintf('%s: Parse error', __METHOD__));
        }

        return $array;
    }
}
