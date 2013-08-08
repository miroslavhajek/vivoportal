<?php
namespace Vivo\Form\Element;

use Zend\Form\Element\Select as ZendSelect;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;
use Zend\Validator\Explode as ExplodeValidator;
use Zend\Validator\InArray as InArrayValidator;
use Zend\Validator\StringLength as StringLengthValidator;

class Select extends ZendSelect implements ElementPrepareAwareInterface
{
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
}
