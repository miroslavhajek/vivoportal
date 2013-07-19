<?php
namespace Vivo\Backend\Form;

use Vivo\Form\Form;
use Vivo\CMS\Validator\SubdocsAllowed;

use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Delete
 * Delete document form
 */
class Delete extends Form implements InputFilterProviderInterface
{
    /**
     * Has the document being deleted any subdocuments?
     * @var bool
     */
    protected $hasSubdocs;

    /**
     * Constructor
     */
    public function __construct($hasSubdocs)
    {
        parent::__construct('deleteDocument');
        $this->hasSubdocs   = (bool) $hasSubdocs;

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name'  => 'delete_subdocs',
            'type'  => 'Zend\Form\Element\Checkbox',
            'options'   => array(
                'label'     => 'Delete subdocuments',
            ),
        ));
        $this->add(array(
            'name'  => 'submit',
            'type'  => 'Zend\Form\Element\Submit',
            'attributes'   => array(
                'value'     => 'Delete',
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
        $valSubdocs = new SubdocsAllowed(array('hasSubdocs' => $this->hasSubdocs));
        $valSubdocs->setMessage('The document has subdocuments', SubdocsAllowed::SUBDOCS_NOT_ALLOWED);
        return array(
            'delete_subdocs'    => array(
                'validators'    => array(
                    $valSubdocs,
                ),
            ),
        );
    }
}
