<?php
namespace Vivo\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Array to key value string
 * Numeric keys are omitted in result string.
 * Only string keys are taken into account.
 */
class ArrayToKeyValueString extends AbstractFilter
{

    /**
     * (non-PHPdoc)
     * @see \Zend\Filter\FilterInterface::filter()
     */
    public function filter($value)
    {
        $string = '';

        $rows = array();
        if (!empty($value) && is_array($value)) {
            foreach ($value as $k => $v) {
                $rowChunks = array();
                if (!is_int($k)) {
                    $rowChunks[] = $k;
                }
                $rowChunks[] = $v;
                $rows[] = implode(" = ", $rowChunks) . PHP_EOL;
            }
            $string = trim(implode('',$rows));
        }

        return $string;
    }
}
