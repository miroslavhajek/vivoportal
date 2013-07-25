<?php
namespace Vivo\Backend\Form\Fieldset;

use Vivo\Form\Fieldset;

use Zend\InputFilter\InputFilterProviderInterface;

/**
 * EntityEditor fieldset.
 */
class EntityEditor extends Fieldset implements InputFilterProviderInterface
{

    /**
     * Input filter specification for fieldset
     *
     * @var array
     */
    protected $inputFilterSpecification = array();

    /**
     * Constructor.
     *
     * @param string $name Fieldset name.
     * @param array $lookupData
     */
    public function __construct($name, array $lookupData)
    {
        parent::__construct($name);

        foreach ($lookupData as $name => $attrs) {
            if(!isset($attrs['field_type'])) {
                continue;
            }

            // Options
            $options = array();

            if(!empty($attrs['label'])) {
                $options['label'] = $attrs['label'];
            }
            if(!empty($attrs['description'])) {
                $options['description'] = $attrs['description'];
            }

            // Attributes
            $attributes = array();
            //The element id will be added automatically by TWB view helper
            //$attributes['id'] = $name;

            if(!empty($attrs['options']) && is_array($attrs['options'])) {
                $attributes['options'] = $attrs['options'];
            }
            if(!empty($attrs['field_attributes']) && is_array($attrs['field_attributes'])) {
                $attributes = array_merge($attributes, $attrs['field_attributes']);
            }
            if(!empty($attrs['important'])) {
                $attributes['class'] = 'important';
            }

            // Input filter specification
            if(!empty($attrs['required'])) {
                $this->inputFilterSpecification[$name]['required'] = (bool) $attrs['required'];
            }
            if(!empty($attrs['allow_empty'])) {
                $this->inputFilterSpecification[$name]['allow_empty'] = (bool) $attrs['allow_empty'];
            }

            // Field init
            $this->add(array(
                'name' => $name,
                'type' => $this->getFieldFqcnByType($attrs['field_type']),
                'options' => $options,
                'attributes' => $attributes,
            ));
        }
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getFieldFqcnByType($type)
    {
        if(strpos($type, '\\') && class_exists($type)) {
            return $type;
        }

        $elementClass = 'Vivo\Form\Element\\'.ucfirst($type);

        return $elementClass;
    }

    /**
     * Returns input filter specification for fieldset
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return $this->inputFilterSpecification;
    }

}

