<?php
namespace Vivo\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Array to key-value string
 * Numeric keys are omitted in result string.
 * Only string keys are taken into account.
 */
class ArrayToKeyValueString extends AbstractFilter
{

    /**
     * Converts array into key-value string representation
     * Numeric keys are ommited
     * @see \Zend\Filter\FilterInterface::filter()
     * @return string
     */
    public function filter($value)
    {
        $string = '';
        foreach ($value as $k => $v) {
            $v = str_replace('"', '\"', $v);
            if (is_int($k)) {
                $string .= sprintf('"%s"', $v);
            } else {
                $string .= sprintf('%s = "%s"', $k, $v);
            }
            $string .= PHP_EOL;
        }
        $string = trim($string);

        return $string;
    }

}
