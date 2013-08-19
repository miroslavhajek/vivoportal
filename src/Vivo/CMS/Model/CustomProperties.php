<?php
namespace Vivo\CMS\Model;

use Vivo\CMS\Exception\InvalidArgumentException;

class CustomProperties implements \Iterator, \Countable
{
    /**
     * @var array
     */
    protected $array = array();

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return array_key_exists($name, $this->array) ? $this->array[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \Vivo\CMS\Exception\InvalidArgumentException
     * @return \Vivo\CMS\Model\CustomProperties
     */
    public function set($name, $value)
    {
        if($name === null || trim($name) == '') {
            throw new InvalidArgumentException(
                sprintf('%s: Custom property name must be set; %s="%s" given', __METHOD__, gettype($name), $name));
        }

        $this->array[$name] = $value;
        return $this;
    }

    public function current()
    {
        return current($this->array);
    }

    public function next()
    {
        return next($this->array);
    }

    public function key()
    {
        return key($this->array);
    }

    public function valid()
    {
        return key($this->array) !== null;
    }

    public function rewind()
    {
        return reset($this->array);
    }

    public function count()
    {
        return count($this->array);
    }
}
