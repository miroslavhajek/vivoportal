<?php
namespace Vivo\Serializer\Adapter;

use Vivo\Serializer\Exception;
use Vivo\Text\Text;

use VpLogger\Log\Logger;

use Zend\Serializer\Adapter\AbstractAdapter;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;

/**
 * Serializer adapter for serializing to Vivo entity format.
 *
 */
class Entity extends AbstractAdapter
{

    const INDENT = "\t";
    const EOA = '__EOA__';
    const NONE = '__NONE__';

    /**
     * Event Manager
     * @var EventManagerInterface
     */
    private $events;

    /**
     * Serialize
     * @param  mixed $value
     * @return string
     */
    public function serialize($entity)
    {
        return $this->serializeRun($entity);
    }

    private function serializeRun($value, $name = false, $depth = 0)
    {
        $out = ($indent = str_repeat(self::INDENT, $depth))
                . ($name ? "$name " : '');
        switch ($type = gettype($value)) {
        case 'NULL':
            return $out . 'NULL';
        case 'boolean':
            return $out . 'boolean ' . ($value ? 'true' : 'false');
        case 'integer':
        case 'long':
        case 'float':
        case 'double':
            return $out . gettype($value) . ' ' . strval($value);
        case 'string':
            $str = strval($value);
            if (substr($str, -1) == '\\')
                $str .= '\\';
            return $out . 'string "' . str_replace('"', '\\"', $str) . '"';
        case 'array':
            $out .= "array (\n";
            foreach ($value as $key => $val) {
                $out .= $indent . self::INDENT . $this->serializeRun($key)
                        . ' : '
                        . ltrim($this->serializeRun($val, false, $depth + 1))
                        . "\n";
            }
            return $out . $indent . ")";
        case 'object':
            $class_name = get_class($value);
            $out .= "object $class_name ";
            $out .= "{\n";

            //@todo: proc?
            // 				if (method_exists($value, '__vivo_sleep')) {
            // 					$vars = array();
            // 					foreach ($value->__vivo_sleep() as $name)
            // 						$vars[$name] = $value->$name;
            // 				} else {

            $props = $this->getObjectProperties($value);
            unset($props['path']);

            foreach ($props as $name => $prop) {
                $prop->setAccessible(true);
                $val = $prop->getValue($value);

                // 					if (substr($name, 0, 2) != '__') //@fixme: proc?
                $out .= $this->serializeRun($val, $name, $depth + 1) . "\n";
            }
            return $out . $indent . '}';
        default:
            throw new Exception\RuntimeException("Unsupported type $type of value $value");
        }
    }

    private function getObjectProperties($className)
    {
        if (is_object($className)) {
            $ref = new \ReflectionObject($className);
        } else {
            $ref = new \ReflectionClass($className);
        }
        $props = $ref->getProperties();
        $props_arr = array();
        foreach ($props as $prop) {
            $name = $prop->getName();
            $props_arr[$name] = $prop;
        }
        if ($parentClass = $ref->getParentClass()) {
            $parent_props_arr = $this
                    ->getObjectProperties($parentClass->getName());
            if (count($parent_props_arr) > 0)
                $props_arr = array_merge($parent_props_arr, $props_arr);
        }
        return $props_arr;
    }

    public function unserialize($serialized)
    {
        $pos    = 0;
        $object = $this->unserializeRun($serialized, $pos, true);
        return $object;
    }

    /**
     * @param string $str
     * @param int $pos
     * @param bool $ignoreUnknownProperties
     * @throws \Vivo\Serializer\Exception\RuntimeException
     * @throws \Exception|\ReflectionException
     * @throws \Vivo\Serializer\Exception\ClassNotFoundException
     * @return array|bool|\DateTime|float|int|mixed|null|string
     */
    protected function unserializeRun(&$str, &$pos = 0, $ignoreUnknownProperties = true)
    {
        switch ($type = Text::readWord($str, $pos)) {
        case 'NULL':
            return null;
        case 'boolean':
            return (Text::readWord($str, $pos) == 'true');
        case 'integer':
            return intval(Text::readWord($str, $pos));
        case 'long':
            return longval(Text::readWord($str, $pos));
        case 'float':
        case 'double':
            return doubleval(Text::readWord($str, $pos));
        case 'string':
            Text::expectChar('"', $str, $pos);
            $len = strlen($str);
            $start = $pos;
            while (!($str{$pos} == '"'
                    && ($str{$pos - 1} != '\\' || $str{$pos - 2} == '\\'))
                    && ($pos < $len))
                $pos++;
            $str2 = str_replace("\\\"", "\"",
                    substr($str, $start, ($pos++) - $start));
            return (substr($str2, -2) == "\\\\") ? substr($str2, 0, -1) : $str2;
        case 'array':
            Text::expectChar('(', $str, $pos);
            $array = array();
            while (($key = $this->unserializeRun($str, $pos, $ignoreUnknownProperties)) !== self::EOA) {
                Text::expectChar(':', $str, $pos);
                $value = $this->unserializeRun($str, $pos, $ignoreUnknownProperties);
                $array[$key] = $value;
            }
            return $array;
        case 'object':
            $className = Text::readWord($str, $pos);
            Text::expectChar('{', $str, $pos);
            if (!class_exists($className)) {
                throw new Exception\ClassNotFoundException(
                    sprintf("%s: Class '%s' not found", __METHOD__, $className));
            }
            $object = new $className;
            $refl   = new \ReflectionObject($object);
            $vars   = array();
            while (($name = Text::readWord($str, $pos)) != '}') {
                try {
                    $prop = $refl->getProperty($name);
                } catch (\ReflectionException $e) {
                    //Property does not exists
                    if ($ignoreUnknownProperties) {
                        $events = $this->getEventManager();
                        $events->trigger('log', $this, array(
                            'message'   => sprintf("Property '%s' not found in an object of class '%s'",
                                $name, $className),
                            'priority'  => Logger::WARN,
                        ));
                        //Read the property value to advance to next property
                        $this->unserializeRun($str, $pos, true);
                        continue;
                    } else {
                        throw $e;
                    }
                }
                $vars[$name] = $this->unserializeRun($str, $pos, $ignoreUnknownProperties);
                $prop->setAccessible(true);
                $prop->setValue($object, $vars[$name]);
            }
            // 				if (method_exists($object, '__wakeup'))
            // 				$object->__wakeup();
            if ($className == 'DateTime') {
                try {
                    $object = new \DateTime($vars['date'], new \DateTimeZone($vars['timezone']));
                } catch (\Exception $e) {
                    $object = new \DateTime;
                }
            }
            return $object;
        case ')': // end of array
            return self::EOA;
        default:
            throw new Exception\RuntimeException(
                    "Undefined type '$type' at position $pos: \""
                            . substr(substr($str, $pos), 20) . "...\"");
        }
    }

    /**
     * Returns Event Manager
     * @return EventManager|EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->events) {
            $this->events   = new EventManager();
        }
        return $this->events;
    }
}
