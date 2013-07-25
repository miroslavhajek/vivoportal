<?php
namespace Vivo\Backend\Form;

use Vivo\Form\Form;
use Vivo\CMS\Model\Entity;
use Vivo\CMS\Model\Document;

use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Move
 * Move document form
 */
class Move extends Form implements InputFilterProviderInterface
{
    /**
     * Constructor
     * @param Entity $entity
     */
    public function __construct(Entity $entity)
    {
        parent::__construct('moveDocument');

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
        if ($entity instanceof Document) {
            $this->add(array(
                'name'          => 'create_hyperlink',
                'type'          => 'Vivo\Form\Element\Checkbox',
                'options'       => array(
                    'label'         => 'Create hyperlink',
                ),
            ));
        }
        $this->add(array(
            'name'  => 'submit',
            'type'  => 'Vivo\Form\Element\Submit',
            'attributes'   => array(
                'value'     => 'Submit',
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
