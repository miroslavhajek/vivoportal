<?php
namespace Vivo\Form;

use Vivo\Form\Form;
use Vivo\Form\Factory as FormFactory;
use Vivo\InputFilter\NewInputFilterFactory;

/**
 * NewFormFactory
 * Creates new instances of properly configured Vivo Forms
 */
class NewFormFactory
{
    /**
     * Factory for form elements, fieldsets, etc.
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * NewInputFilterFactory
     * @var NewInputFilterFactory
     */
    protected $newInputFilterFactory;

    /**
     * Constructor
     * @param Factory $formFactory
     * @param NewInputFilterFactory $newInputFilterFactory
     */
    public function __construct(FormFactory $formFactory, NewInputFilterFactory $newInputFilterFactory)
    {
        $this->formFactory              = $formFactory;
        $this->newInputFilterFactory    = $newInputFilterFactory;
    }

    /**
     * Creates, configures and returns a new instance of Vivo Form
     * @param string|null $name
     * @param array $options
     * @return Form
     */
    public function create($name = null, array $options = array())
    {
        $inputFilter    = $this->newInputFilterFactory->create();
        $form           = new Form($name, $options);
        $form->setFormFactory($this->formFactory);
        $form->setInputFilter($inputFilter);
        return $form;
    }
}
