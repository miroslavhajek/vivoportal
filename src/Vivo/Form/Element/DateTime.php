<?php
namespace Vivo\Form\Element;

use Zend\Form\Element\DateTime as ZendDateTime;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;

class DateTime extends ZendDateTime implements ElementPrepareAwareInterface
{
    /**
     * DateTime format
     * @var string
     */
    protected $format   = 'Y-m-d H:i';

    /**
     * @see \Zend\Form\Element::setOptions()
     */
    public function setOptions($options)
    {
        if(isset($options['format'])) {
            $this->setFormat($options['format']);
            unset($options['format']);
        }

        parent::setOptions($options);
        return $this;
    }

    public function getFilters()
    {
        return array(
            array('name' => 'Zend\Filter\StringTrim'),
            array('name' => 'Vivo\Filter\DateTime'), //TODO: set format from options
        );
    }

    protected function getValidators()
    {
        return array();
    }

    public function getInputSpecification()
    {
        return array(
            'name' => $this->getName(),
            'required' => false,
            'filters' => $this->getFilters(),
            'validators' => $this->getValidators(),
        );
    }

    /**
     * Prepare the form element (mostly used for rendering purposes)
     * @param FormInterface $form
     * @return mixed
     */
    public function prepareElement(FormInterface $form)
    {
        //Add 'id' attribute
        if (!$this->getAttribute('id')) {
            $id = str_replace(']', '', $this->getName());
            $id = str_replace('[', '-', $id);
            $this->setAttribute('id', $id);
        }
    }
}
