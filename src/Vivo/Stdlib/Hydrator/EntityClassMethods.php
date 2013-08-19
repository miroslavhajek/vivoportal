<?php
namespace Vivo\Stdlib\Hydrator;

use Vivo\CMS\Model\CustomProperties;
use Zend\Stdlib\Hydrator\ClassMethods as ZendClassMethods;
use Zend\Stdlib\Exception;
use Zend\Stdlib\Hydrator\Filter\FilterComposite;
use Zend\Stdlib\Hydrator\Filter\FilterProviderInterface;
use Zend\Stdlib\Hydrator\Filter\MethodMatchFilter;

class EntityClassMethods extends ZendClassMethods
{
    /**
     * Extract values from an object with class methods
     *
     * Extracts the getter/setter of the given $object.
     *
     * @param  object                           $object
     * @return array
     * @throws \Zend\Stdlib\Exception\BadMethodCallException for a non-object $object
     */
    public function extract($object)
    {
        if (!is_object($object)) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided $object to be a PHP object)', __METHOD__
            ));
        }

        $filter = null;
        if ($object instanceof FilterProviderInterface) {
            $filter = new FilterComposite(
                array($object->getFilter()),
                array(new MethodMatchFilter('getFilter'))
            );
        } else {
            $filter = $this->filterComposite;
        }

        $transform = function ($letters) {
            $letter = array_shift($letters);

            return '_' . strtolower($letter);
        };
        $attributes = array();
        $methods = get_class_methods($object);

        foreach ($methods as $method) {
            $classMethod = get_class($object).'::'.$method;
            if (!$filter->filter($classMethod)) {
                continue;
            }

            $reflectionMethod = new \ReflectionMethod($classMethod);
            if ($reflectionMethod->getNumberOfParameters() > 0) {
                continue;
            }

            $attribute = $method;
            if (preg_match('/^get/', $method)) {
                $attribute = substr($method, 3);
                $attribute = lcfirst($attribute);
            }

            if ($this->underscoreSeparatedKeys) {
                $attribute = preg_replace_callback('/([A-Z])/', $transform, $attribute);
            }

            $return = $object->$method();
            if($return instanceof CustomProperties) {
                foreach ($return as $name => $value) {
                    $attributes[$name] = $this->extractValue($attribute, $value);
                }
            }
            else {
                $attributes[$attribute] = $this->extractValue($attribute, $return);
            }
        }

        return $attributes;
    }

    /**
     * Hydrate an object by populating getter/setter methods
     *
     * Hydrates an object by getter/setter methods of the object.
     *
     * @param  array                            $data
     * @param  object                           $object
     * @return object
     * @throws \Zend\Stdlib\Exception\BadMethodCallException for a non-object $object
     */
    public function hydrate(array $data, $object)
    {
        if (!is_object($object)) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided $object to be a PHP object)', __METHOD__
            ));
        }

        $transform = function ($letters) {
            $letter = substr(array_shift($letters), 1, 1);

            return ucfirst($letter);
        };

        foreach ($data as $property => $value) {
            $method = 'set' . ucfirst($property);
            if ($this->underscoreSeparatedKeys) {
                $method = preg_replace_callback('/(_[a-z])/', $transform, $method);
            }

            $value = $this->hydrateValue($property, $value);
            $object->$method($value);
        }

        return $object;
    }

}
