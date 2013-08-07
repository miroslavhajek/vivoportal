<?php
namespace Vivo\Form\Element;

use Zend\Form\Element\Time as ZendTime;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;

class Time extends ZendTime implements ElementPrepareAwareInterface
{
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
