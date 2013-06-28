<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\CMS\Api;
use Vivo\CMS\Model;
use Vivo\CMS\Model\Content\Search as SearchModel;
use Vivo\UI\AbstractForm;
use Vivo\Form\Form;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class Search extends AbstractForm implements EditorInterface
{
    /**
     * @var \Vivo\CMS\Model\Content\Search
     */
    private $content;
    
    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;    

    public function __construct(Api\Document $documentApi)
    {
        $this->documentApi = $documentApi;
    }    

    public function init()
    {
        $this->getForm()->bind($this->content);

        parent::init();
    }
    
    /**
     * Saves content
     * @param $content Model\Content
     */
    public function setContent(Model\Content $content)
    {
        $this->content = $content;
    }
    
    /**
     * (non-PHPdoc)
     * @see Vivo\CMS\UI\Content\Editor.EditorInterface::save()
     */
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
    
    /**
     * (non-PHPdoc)
     * @see Vivo\UI.AbstractForm::doGetForm()
     */
    public function doGetForm()
    {
        $form = new Form('editor-'.$this->content->getUuid());
        $form->setWrapElements(true);
        $form->setHydrator(new ClassMethodsHydrator(false));
        $form->setOptions(array('use_as_base_fieldset' => true));
        $form->add(array(
            'name' => 'pageSize',
            'type' => 'Vivo\Form\Element\Select',
            'options' => array('label' => 'Page size'),
            'attributes' => array(
                'options' => SearchModel::$PAGE_SIZE_OPTIONS
            )
        ));
        $form->add(array(
            'name' => 'operator',
            'type' => 'Vivo\Form\Element\Select',
            'options' => array('label' => 'Search results'),
            'attributes' => array(
                'options' => array(
                    SearchModel::EXACT_OP => SearchModel::EXACT_OP,
                    SearchModel::AND_OP => SearchModel::AND_OP,
                    SearchModel::OR_OP => SearchModel::OR_OP,
                )
            )
        ));
        $form->add(array(
            'name' => 'wildcards',
            'type' => 'Vivo\Form\Element\Select',
            'options' => array('label' => 'Condition for words parts'),
            'attributes' => array(
                'options' => array(
                    SearchModel::AS_IS => SearchModel::AS_IS,
                    SearchModel::START_ASTERISK => SearchModel::START_ASTERISK,
                    SearchModel::END_ASTERISK => SearchModel::END_ASTERISK,
                    SearchModel::START_END_ASTERISK => SearchModel::START_END_ASTERISK,
                )
            )
        ));
        $form->add(array(
            'name' => 'resourceLink',
            'type' => 'Vivo\Form\Element\Checkbox',
            'options' => array('label' => 'Resource link'),
        ));

        return $form;
    }

}
