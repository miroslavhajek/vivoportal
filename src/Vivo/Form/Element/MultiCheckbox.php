<?php
namespace Vivo\Form\Element;

use Zend\Form\Element\MultiCheckbox as ZendMultiCheckbox;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;
use Zend\Validator;

class MultiCheckbox extends ZendMultiCheckbox implements ElementPrepareAwareInterface
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
                $notEmptyValidator = new Validator\NotEmpty(array('type' => \Zend\Validator\NotEmpty::NULL));

                $this->validator = new Validator\Explode(array(
                    'validator'      => $notEmptyValidator,
                    'valueDelimiter' => null, // skip explode if only one value
                ));
            }
        }
        return $this->validator;
    }
}
