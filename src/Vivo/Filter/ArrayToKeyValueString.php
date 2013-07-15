<?php
namespace Vivo\Filter;

use Zend\Filter\AbstractFilter;

class ArrayToKeyValueString extends AbstractFilter
{
    /**
     * (non-PHPdoc)
     * @see \Zend\Filter\FilterInterface::filter()
     */
    public function filter($value)
    {
        $string = '';
        foreach ($value as $k => $v) {
            $string.= sprintf('%s = "%s"', $k, str_replace('"', '\"', $v)).PHP_EOL;
        }
        $string = trim($string);

        return $string;
    }
}
