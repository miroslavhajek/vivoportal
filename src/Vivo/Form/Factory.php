<?php
namespace Vivo\Form;

use ArrayAccess;
use Traversable;
use Zend\Form\Factory as ZendFactory;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;

class Factory extends ZendFactory
{
    /**
     * Create an element, fieldset, or form
     *
     * Introspects the 'type' key of the provided $spec, and determines what
     * type is being requested; if none is provided, assumes the spec
     * represents simply an element.
     *
     * @param  array|Traversable $spec
     * @return ElementInterface
     * @throws Exception\DomainException
     */
    public function create($spec)
    {
        if (!isset($spec['type'])) {
            $spec['type'] = 'Vivo\Form\Element';
        }

        return parent::create($spec);
    }

    /**
     * Create an element based on the provided specification
     *
     * Specification can contain any of the following:
     * - type: the Element class to use; defaults to \Zend\Form\Element
     * - name: what name to provide the element, if any
     * - options: an array, Traversable, or ArrayAccess object of element options
     * - attributes: an array, Traversable, or ArrayAccess object of element
     *   attributes to assign
     *
     * @param  array|Traversable|ArrayAccess $spec
     * @return ElementInterface
     * @throws Exception\InvalidArgumentException for an invalid $spec
     * @throws Exception\DomainException for an invalid element type
     */
    public function createElement($spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);

        $type       = isset($spec['type'])       ? $spec['type']       : 'Vivo\Form\Element';
        $name       = isset($spec['name'])       ? $spec['name']       : null;
        $options    = isset($spec['options'])    ? $spec['options']    : null;
        $attributes = isset($spec['attributes']) ? $spec['attributes'] : null;

        $element = new $type();
        if (!$element instanceof ElementInterface) {
            throw new Exception\DomainException(sprintf(
                    '%s expects an element type that implements Zend\Form\ElementInterface; received "%s"',
                    __METHOD__,
                    $type
            ));
        }

        if ($name !== null && $name !== '') {
            $element->setName($name);
        }

        if($type == 'Vivo\Form\Element\DateTime' && empty($options['format'])) {
            $options['format'] = 'Y.m.d'; //TODO
        }

        if (is_array($options) || $options instanceof Traversable || $options instanceof ArrayAccess) {
            $element->setOptions($options);
        }

        if (is_array($attributes) || $attributes instanceof Traversable || $attributes instanceof ArrayAccess) {
            $element->setAttributes($attributes);
        }

        return $element;
    }

    /**
     * Create a fieldset
     *
     * @param  array $spec
     * @return ElementInterface
     */
    public function createFieldset($spec)
    {
        if (!isset($spec['type'])) {
            $spec['type'] = 'Vivo\Form\Fieldset';
        }

        return parent::createFieldset($spec);
    }

    /**
     * Create a form
     *
     * @param  array $spec
     * @return ElementInterface
     */
    public function createForm($spec)
    {
        if (!isset($spec['type'])) {
            $spec['type'] = 'Vivo\Form\Form';
        }

        return parent::createForm($spec);
    }
}
