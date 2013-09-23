<?php
namespace Vivo\Serializer\Adapter;

use Vivo\Serializer\Exception;
use Vivo\Text\Text;
use Vivo\Metadata\MetadataManager;
use Vivo\CMS\Model;

use VpLogger\Log\Logger;

use Zend\Serializer\Adapter\AbstractAdapter;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;

/**
 * Serializer adapter for serializing to Vivo entity format.
 */
class Entity extends AbstractAdapter
{
    const INDENT    = "\t";
    const EOA       = '__EOA__';
    const NONE      = '__NONE__';

    /**
     * Text service
     * @var \Vivo\Text\Text
     */
    protected $textService;

    /**
     * Metadata manager service
     * @var \Vivo\Metadata\MetadataManager
     */
    protected $metadataManager;

    /**
     * Event Manager
     * @var EventManagerInterface
     */
    private $events;

    /**
     * Constructor
     * @param \Vivo\Text\Text $textService
     * @param \Vivo\Metadata\MetadataManager $metadataManager
     * @param mixed $options
     */
    public function __construct(Text $textService, MetadataManager $metadataManager, $options = null)
    {
        parent::__construct($options);
        $this->textService  = $textService;
        $this->metadataManager = $metadataManager;
    }

    /**
     * Serialize
     * @param mixed $value
     * @return string
     */
    public function serialize($entity)
    {
        return $this->serializeRun($entity);
    }

    private function serializeRun($value, $name = false, $depth = 0)
    {
        $out = ($indent = str_repeat(self::INDENT, $depth)).($name ? "$name " : '');
        $type = gettype($value);

        switch ($type) {
            case 'NULL':
                return $out . 'NULL';
            case 'boolean':
                return $out . 'boolean ' . ($value ? 'true' : 'false');
            case 'integer':
            case 'long':
            case 'float':
            case 'double':
                return $out . $type . ' ' . strval($value);
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

                $allowed = array();
                if (method_exists($value, '__sleep')) {
                    $allowed = $value->__sleep();
                    $allowed = is_array($allowed) ? $allowed : array();
                }

                $props = $this->getObjectProperties($value);
                foreach ($props as $name => $prop) {
                    if(in_array($name, $allowed)) {
                        $prop->setAccessible(true);
                        $val = $prop->getValue($value);

                        $out .= $this->serializeRun($val, $name, $depth + 1) . "\n";
                    }
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
            $parent_props_arr = $this->getObjectProperties($parentClass->getName());
            if (count($parent_props_arr) > 0)
                $props_arr = array_merge($parent_props_arr, $props_arr);
        }
        return $props_arr;
    }

    /**
     * Unserialize
     * @param string
     * @return object
     */
    public function unserialize($serialized)
    {
        $eventManager = $this->getEventManager();
        $eventManager->trigger('log:start', $this, array('subject' => 'serializer:unserialize'));
        $pos    = 0;
        $object = $this->unserializeRun($serialized, $pos, true);
        $eventManager->trigger('log:stop', $this, array('subject' => 'serializer:unserialize'));
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
        switch ($type = $this->textService->readWord($str, $pos)) {
        case 'NULL':
            return null;
        case 'boolean':
            return ($this->textService->readWord($str, $pos) == 'true');
        case 'integer':
            return intval($this->textService->readWord($str, $pos));
        case 'long':
            return intval($this->textService->readWord($str, $pos));
        case 'float':
        case 'double':
            return floatval($this->textService->readWord($str, $pos));
        case 'string':
            $this->textService->expectChar('"', $str, $pos);
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
            $this->textService->expectChar('(', $str, $pos);
            $array = array();
            while (($key = $this->unserializeRun($str, $pos, $ignoreUnknownProperties)) !== self::EOA) {
                $this->textService->expectChar(':', $str, $pos);
                $value = $this->unserializeRun($str, $pos, $ignoreUnknownProperties);
                $array[$key] = $value;
            }
            return $array;
        case 'object':
            $className = $this->textService->readWord($str, $pos);
            $this->textService->expectChar('{', $str, $pos);
            if (!class_exists($className)) {
                throw new Exception\ClassNotFoundException(sprintf("%s: Class '%s' not found", __METHOD__, $className));
            }

            $object = new $className;

            if($object instanceof Model\Entity && $this->metadataManager->getCustomPropertiesDefs($className)) {
                $customProperties = $this->metadataManager->getCustomProperties($className);
                $object->setAllowedCustomProperties(array_keys($customProperties));
            }

            $refl   = new \ReflectionObject($object);
            $vars   = array();
            while (($name = $this->textService->readWord($str, $pos)) != '}') {
                try {
                    $prop = $refl->getProperty($name);
                } catch (\ReflectionException $e) {
                    //Property does not exists
                    if ($ignoreUnknownProperties) {
                        $eventManager = $this->getEventManager();
                        $eventManager->trigger('log', $this, array(
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
                sprintf("Undefined type '%s' at position %s: '%s...'", $type, $pos, substr(substr($str, $pos), 20)));
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
