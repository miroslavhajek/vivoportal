<?php
namespace Vivo\Filter;

use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception;

/**
 * AsciiAlphaNum
 * Filters out everything except ASCII alphanumeric characters
 */
class AsciiAlphaNum extends AbstractFilter
{
    /**
     * Returns the result of filtering $value
     * @param  mixed $value
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        $pattern = '/[^0-9a-zA-Z]/';
        return preg_replace($pattern, '', (string) $value);
    }
}
