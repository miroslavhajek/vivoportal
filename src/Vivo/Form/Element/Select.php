<?php
namespace Vivo\Form\Element;

use Vivo\Form\ValidationResultAwareInterface;

use Zend\Form\Element\Select as ZendSelect;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;
use Zend\Validator\Explode as ExplodeValidator;
use Zend\Validator\InArray as InArrayValidator;
use Zend\Validator\StringLength as StringLengthValidator;

class Select extends ZendSelect implements ElementPrepareAwareInterface, ValidationResultAwareInterface
{
    /**
     * Flag indicating the last validation result, null means validation not performed
     * @var bool|null
     */
    protected $validationResult;

    /**
     * Should the default InArray validator be disabled?
     * ZF2 2.2 provides this feature out of the box
     * @var bool
     */
    protected $disableInArrayValidator  = false;

    /**
     * Disable the default InArray validator
     * @param bool $disable
     */
    public function setDisableInArrayValidator($disable)
    {
        $this->disableInArrayValidator  = (bool) $disable;
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

    /**
     * Overridden getValidator() to disable the default InArray validator
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator) {
            if (!$this->disableInArrayValidator) {
                parent::getValidator();
            } else {
                    $validator = new StringLengthValidator(array('min' => 0));
                    $this->validator = $validator;
            }
        }
        return $this->validator;
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
