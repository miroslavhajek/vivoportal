<?php
namespace Vivo\Form\Element;

use Vivo\Filter\ArrayToKeyValueString;
use Zend\InputFilter\InputProviderInterface;

class ArrayInTextarea extends Textarea implements InputProviderInterface
{
    /**
     * @param array $value
     * @throws \Vivo\Form\Exception\InvalidArgumentException
     * @return \Zend\Form\Element
     */
    public function setValue($value)
    {
        if(is_array($value)) {
            //TODO: move to factory
            $filter = new ArrayToKeyValueString();
            $value = $filter->filter($value);
        }

        parent::setValue($value);
    }

    public function getInputSpecification()
    {
        return array(
             'name' => $this->getName(),
             'required' => false,
             'filters' => array(
                 array('name' => 'Vivo\Filter\KeyValueStringToArray'),
             ),
         );
    }
}
