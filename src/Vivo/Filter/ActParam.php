<?php
namespace Vivo\Filter;

use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception;

/**
 * ActParam
 * Filters 'act' param sent to front controller
 */
class ActParam extends AbstractFilter
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        //Build part of the RE from the component separator
        $rePart     = '';
        foreach (str_split(\Vivo\UI\Component::COMPONENT_SEPARATOR) as $char) {
            $rePart .= '\\' . $char;
        }
        $pattern    = sprintf('/[^ 0-9a-zA-Z%s]/', $rePart);
        return preg_replace($pattern, '', (string) $value);
    }
}
