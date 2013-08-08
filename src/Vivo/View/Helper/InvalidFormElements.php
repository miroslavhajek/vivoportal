<?php
namespace Vivo\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\FormInterface;

/**
 * InvalidFormElements
 * Processes invalid form elements
 */
class InvalidFormElements extends AbstractHelper
{
    /**
     * Default invocation of this view helper
     * @param FormInterface $form
     * @return $this|array
     */
    public function __invoke(FormInterface $form = null)
    {
        if (is_null($form)) {
            return $this;
        }
        $errorMessages   = $this->getErrorMessages($form);
        return $errorMessages;
    }

    /**
     * Returns all invalid elements and their error messages in a structured array ready for dumping to output
     * This method is useful debugging
     * @param FormInterface $form
     * @return array
     */
    public function getErrorMessages(FormInterface $form)
    {
        $inputFilter    = $form->getInputFilter();
        $invalidInputs  = $inputFilter->getInvalidInput();
        $invalidInputs  = $this->expandInvalidInputs($invalidInputs);
        return $invalidInputs;
    }

    /**
     * Recursively expands invalid inputs and returns their error messages in a structured array
     * @param array $invalidInputs
     * @return array
     */
    protected function expandInvalidInputs(array $invalidInputs)
    {
        foreach ($invalidInputs as $name => $input) {
            if ($input instanceof \Zend\InputFilter\InputFilterInterface) {
                $childInvalidInputs     = $input->getInvalidInput();
                $invalidInputs[$name]   = $this->expandInvalidInputs($childInvalidInputs);
            } else {
                /** @var $input \Zend\InputFilter\InputInterface */
                $invalidInputs[$name]   = $input->getMessages();
            }
        }
        return $invalidInputs;
    }
}
