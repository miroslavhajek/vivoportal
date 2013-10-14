<?php
namespace Vivo\Form;

//use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

/**
 * Logon
 * Logon form
 */
class Logon extends Form
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('logon');

        $this->setAttribute('method', 'post');
        $inputFilter    = new InputFilter();
        $this->setInputFilter($inputFilter);

        //Logon fieldset
        $this->add(array(
            'type'      => 'Vivo\Form\Fieldset\Logon',
            'name'      => 'logon',
            'options'   => array(
                'use_as_base_fieldset'  => true,
            ),
        ));

        //Submit
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => gettext_noop('Login'),
            ),
        ));
    }
}
