<?php
namespace Vivo\Backend\Form;

use Vivo\Form\Form;

use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Copy
 * Copy document form
 */
class Copy extends Form implements InputFilterProviderInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('copyDocument');

        $this->setAttribute('method', 'post');
        $this->add(array(
            'name'          => 'path',
            'attributes'    => array(
                'type'          => 'text',
            ),
            'options'       => array(
                'label'         => 'Path',
            ),
        ));
        $this->add(array(
            'name'          => 'name',
            'attributes'    => array(
                'type'          => 'text',
            ),
            'options'       => array(
                'label'         => 'Name',
            ),
        ));
        $this->add(array(
            'name'          => 'name_in_path',
            'attributes'    => array(
                'type'          => 'text',
            ),
            'options'       => array(
                'label'         => 'Name in path',
            ),
        ));
        $this->add(array(
            'name'  => 'submit',
            'type'  => 'Vivo\Form\Element\Submit',
            'attributes'   => array(
                'value'     => 'Copy',
            ),
        ));
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            'path'    => array(
                'required'      => true,
                'validators'    => array(
                ),
            ),
            'name'    => array(
                'required'      => true,
                'validators'    => array(
                ),
            ),
            'name_in_path'    => array(
                'required'      => true,
                'validators'    => array(
                ),
            ),
        );
    }
}
