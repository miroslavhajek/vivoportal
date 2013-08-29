<?php
namespace Vivo\Form\Element;

use Vivo\Form\ValidationResultAwareInterface;

use Zend\Form\Element\Text as ZendText;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;

class Text extends ZendText implements ElementPrepareAwareInterface, ValidationResultAwareInterface
{
    /**
     * Flag indicating the last validation result, null means validation not performed
     * @var bool|null
     */
    protected $validationResult;

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

    /**
     * Sets result of the last validation, null means validation not performed
     * @param bool|null $validationResult
     * @return void
     */
    public function setValidationResult($validationResult)
    {
        if (!is_null($validationResult)) {
            $validationResult   = (bool) $validationResult;
        }
        $this->validationResult = $validationResult;
    }

    /**
     * Returns result of the last validation, null means validation not performed
     * @return bool|null
     */
    public function getValidationResult()
    {
        return $this->validationResult;
    }
}
