<?php
namespace Vivo\Form\Element;

use Zend\Form\Element\Radio as ZendRadio;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;

class Radio extends ZendRadio implements ElementPrepareAwareInterface
{
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
