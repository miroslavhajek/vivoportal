<?php
namespace Vivo\Filter;

use Zend\Filter\AbstractFilter;
use Zend\Filter\Exception\RuntimeException;

/**
 * Key-value string to array filter
 * Keys can be ommited
  */
class KeyValueStringToArray extends AbstractFilter
{

    /**
     * Regular expression used for matching particular row in textarea
     * @var string
     */
    protected $pattern = '(?:[\t\ ]*([\w]+)[\t\ ]*=)?[\t\ ]*"?([^"]+)"?[\t\ ]*';

    /**
     * Decodes input string into array
     * @param string $input
     * @throws \Zend\Filter\Exception\RuntimeException
     */
    protected function decodeString($input)
    {
        $input = str_replace("\r", '', $input);
        $re = '~^' . $this->pattern . '$~';

        $rows = explode("\n", $input);

        $array = array();
        $key = $value = null;

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $hasKey = false;
                if (!empty($row)) {
                    if (preg_match($re, $row, $matches)) {
                        $key = $matches[1];
                        $value = $matches[2];

                        // check key
                        // omit numeric keys and empty keys
                        if (empty($key) || is_numeric($key)) {
                            $hasKey = false;
                        } else {
                            $hasKey = true;
                        }

                        // remove quotes from value
                        if ($value[0] == '"' || $value[0] == "'") {
                            $value = mb_substr($value, 1, -1);
                        }

                        // insert value
                        if ($hasKey) {
                            if (array_key_exists($key, $array))
                                throw new RuntimeException("Duplicated key '$key'");
                            $array[$key] = $value;
                        } else {
                            $array[] = $value;
                        }
                    } else {
                        // row is not matched -> error
                        throw new RuntimeException(sprintf('%s: Parse error', __METHOD__));
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Returns decoded array
     * @see \Zend\Filter\FilterInterface::filter()
     * @throws \Zend\Filter\Exception\RuntimeException
     * @return array Decoded array
     */
    public function filter($value)
    {
        return $this->decodeString($value);
    }
}
