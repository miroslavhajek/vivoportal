<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\CMS\Api;
use Vivo\CMS\Model;
use Vivo\UI\AbstractForm;
use Vivo\Form\Form;
use Vivo\CMS\Model\Content\Navigation as NavigationModel;

use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

/**
 * Class Navigation
 * @package Vivo\CMS\UI\Content\Editor
 */
class Navigation extends AbstractForm implements EditorInterface
{
    /**
     * @var \Vivo\CMS\Model\Content\Navigation
     */
    protected $content;

    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;

    /**
     * When set to true, CSRF protection will be automatically added to the form
     * Redefine in descendant if necessary
     * @var bool
     */
    protected $autoAddCsrf  = false;

    /**
     * Native document sorting options
     * @var array
     */
    protected $sortOptions = array();

    /**
     * Constructor
     * @param Api\Document $documentApi
     */
    public function __construct(Api\Document $documentApi, array $sortOptions)
    {
        $this->documentApi = $documentApi;
        $this->sortOptions = $sortOptions;
    }

    public function setContent(Model\Content $content)
    {
        $this->content = $content;
    }

    public function init()
    {
        $this->getForm()->bind($this->content);

        parent::init();
    }

    public function save(Model\ContentContainer $contentContainer)
    {
        if($this->getForm()->isValid()) {
            if($this->content->getUuid()) {
                $this->documentApi->saveContent($this->content);
            }
            else {
                $this->documentApi->createContent($contentContainer, $this->content);
            }
        }
    }

    public function doGetForm()
    {
        $form = new Form('editor-'.$this->content->getUuid());
        $form->setWrapElements(true);
        $form->setHydrator(new ClassMethodsHydrator(false));
        $form->setOptions(array('use_as_base_fieldset' => true));
        //Type
        $form->add(array(
            'name' => 'type',
            'type' => 'Vivo\Form\Element\Select',
            'options' => array(
                'label' => 'Type',
                'value_options' => array(
                    NavigationModel::TYPE_ORIGIN    => 'Specified origin or current document',
                    NavigationModel::TYPE_ENUM      => 'Enumerated documents',
                ),
            ),
        ));
        //Origin
        $form->add(array(
            'name'      => 'origin',
            'type'      => 'Vivo\Form\Element\Text',
            'options'   => array(
                'label' => 'Origin document path',
                'description'   => 'Path of an entity which is the origin for the navigation tree calculation. '
                                    . 'If not set, the current document is assumed as origin.',
            ),
        ));
        //Start Level
        $form->add(array(
            'name'      => 'startLevel',
            'type'      => 'Vivo\Form\Element\Text',
            'options'   => array(
                'label' => 'Start level',
                'description'   => 'Where to start building the navigation? Zero = Current document or origin, '
                                   . 'Positive = Absolute level (levels start at 1), '
                                   . 'Negative = Relative level - this number of levels up from the origin',
            ),
        ));
        //Branch only
        $form->add(array(
            'name'      => 'branchOnly',
            'type'      => 'Vivo\Form\Element\Checkbox',
            'options'   => array(
                'label' => 'Branch only',
                'description'   => 'Only a single branch of documents will be included in the navigation tree. '
                                   . 'Used mainly for breadcrumbs.',
            ),
        ));
        //Levels
        $form->add(array(
            'name'      => 'levels',
            'type'      => 'Vivo\Form\Element\Text',
            'options'   => array(
                'label' => 'Number of levels',
                'description'   => 'Number of levels to include in the navigation',
            ),
        ));
        //Include root
        $form->add(array(
            'name'      => 'includeRoot',
            'type'      => 'Vivo\Form\Element\Checkbox',
            'options'   => array(
                'label' => 'Include root',
                'description'   => 'Should the root document be included in the navigation?',
            ),
        ));
        //Levels
        $form->add(array(
            'name'      => 'limit',
            'type'      => 'Vivo\Form\Element\Text',
            'options'   => array(
                'label' => 'Limit',
                'description'   => 'Number of documents listed in the navigation in each level. '
                                    . 'Empty means unlimited.',
            ),
        ));
        // Navigation sorting
        $form->add(array(
            'name' => 'navigationSorting',
            'type' => 'Vivo\Form\Element\Select',
            'options' => array('label' => 'sorting'),
            'attributes' => array(
                'options' => $this->sortOptions
            )
        ));
        return $form;
    }

}
