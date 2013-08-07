<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\CMS\Api;
use Vivo\CMS\Model;
use Vivo\UI\AbstractForm;
use Vivo\Form\NewFormFactory;

class Layout extends AbstractForm implements EditorInterface
{
    /**
     * @var \Vivo\CMS\Model\Content\Layout
     */
    private $content;
    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;

    /**
     * New form factory
     * @var NewFormFactory
     */
    protected $newFormFactory;

    /**
     * Constructor
     * @param \Vivo\CMS\Api\Document $documentApi
     * @param \Vivo\Form\NewFormFactory $newFormFactory
     */
    public function __construct(Api\Document $documentApi, NewFormFactory $newFormFactory)
    {
        $this->documentApi = $documentApi;
        $this->newFormFactory = $newFormFactory;
        $this->autoAddCsrf = false;
    }

    /**
     * Sets contest
     * @param \Vivo\CMS\Model\Content $content
     */
    public function setContent(Model\Content $content)
    {
        $this->content = $content;
    }

    /**
     * Initialization
     */
    public function init()
    {
        $this->getForm()->get('panels')->setValue($this->content->getPanels());
        parent::init();
    }

    /**
     * Save
     * @param \Vivo\CMS\Model\ContentContainer $contentContainer
     */
    public function save(Model\ContentContainer $contentContainer)
    {
        $form = $this->getForm();

        if($form->isValid()) {
            $data = $form->getData();
            $this->content->setPanels($data['panels']);
            if($this->content->getUuid()) {
                $this->documentApi->saveContent($this->content);
            }
            else {
                $this->documentApi->createContent($contentContainer, $this->content);
            }
        }
    }

    /**
     *
     * @return \Vivo\Form\Form
     */
    public function doGetForm()
    {
        $form = $this->newFormFactory->create('editor-'.$this->content->getUuid());
        $form->setWrapElements(true);
        $form->setOptions(array('use_as_base_fieldset' => true));
        $form->add(array(
            'name' => 'panels',
            'type' => 'Vivo\Form\Element\ArrayInTextarea',
            'options' => array(
                'label' => 'panels',
                'rows' => 10,
                'cols' => 5,
            ),
        ));

        return $form;
    }
}
