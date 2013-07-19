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
     * Set of regular expressions used to split the input string into tokens
     * @var array
     */
    protected $patterns = array(
        '
            \'[^\'\n]*\' |
            "(?: \\\\. | [^"\\\\\n] )*"
        ', // string
        '
            (?: [^"\'=\x00-\x20] )
            (?:
                    [^=\x00-\x20]+ |
                    (?! [\s,] | $ ) |
                    [\ \t]+ [^=\x00-\x20]
            )*
        ', // literal / boolean / integer / float
        '=', // key-value delimiter
        '\n[\t\ ]*', // new line + indent
        '?:[\t\ ]+', // whitespace
    );

    /**
     * Result
     * @var array
     */
    protected $result;

    /**
     * Decodes input string into array
     * @param string $input
     */
    protected function decodeString($input)
    {
        $input = str_replace("\r", '', $input);
        $re = '~(' . implode(')|(', $this->patterns) . ')~Amix';

        $tokens = preg_split($re, $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $tokens[] = "\n"; // add last line feed

        // store results to $this->result
        $this->parseTokens($tokens);
    }

    /**
     * Parse tokens into result array
     * @param array $tokens
     * @throws RuntimeException
     */
    protected function parseTokens($tokens) {
        $value = $key = $object = NULL;
        $hasValue = $hasKey = FALSE;

        foreach($tokens as $token) {
            if ($token[0] === "\n") {       // New line
                if ($hasKey || $hasValue) {
                    $this->addValue($this->result, $hasKey, $key, $hasValue ? $value : NULL);
                    $hasKey = $hasValue = FALSE;
                }
            } elseif ($token[0] === '=') { // KeyValuePair separator
                if ($hasKey || !$hasValue) {
                    throw new RuntimeException(sprintf('%s: Parse error', __METHOD__));
                }
                $key = (string) $value;
                $hasKey = TRUE;
                $hasValue = FALSE;
            } else {                        // Value
                if ($hasValue) {
                    throw new RuntimeException(sprintf('%s: Parse error', __METHOD__));
                }
                $value = $token;
                $hasValue = TRUE;
            }
        }
    }

    /**
     * Adds value item into $result array
     * @param array $result
     * @param bool $hasKey
     * @param mixed $key
     * @param mixed $value
     * @throws RuntimeException
     */
    protected function addValue(&$result, $hasKey, $key, $value)
    {
        if ($hasKey) {
            if ($result && array_key_exists($key, $result)) {
                throw new RuntimeException("Duplicated key '$key'");
            }
            $result[$key] = $value;
        } else {
            $result[] = $value;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Filter\FilterInterface::filter()
     * @throws \Zend\Filter\Exception\RuntimeException
     */
    public function filter($value)
    {
        $this->result = array();
        $this->decodeString($value);
        return $this->result;
    }
}
