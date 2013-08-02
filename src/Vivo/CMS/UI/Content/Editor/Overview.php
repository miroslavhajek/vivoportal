<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\CMS\Api;
use Vivo\CMS\Model;
use Vivo\CMS\Model\Content\Overview as OverviewModel;
use Vivo\UI\AbstractForm;
use Vivo\Form\Form;
use Vivo\Stdlib\Hydrator\EntityClassMethods as ClassMethodsHydrator;

class Overview extends AbstractForm implements EditorInterface
{
    /**
     * @var \Vivo\CMS\Model\Content\Overview
     */
    private $content;
    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;
    
    /**
     * Native document sorting options
     * @var array
     */
    private $sortOptions = array();

    public function __construct(Api\Document $documentApi, array $sortOptions)
    {
        $this->sortOptions = $sortOptions;
        $this->documentApi = $documentApi;
        $this->autoAddCsrf = false;
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
        $form->add(array(
            'name' => 'overviewType',
            'type' => 'Vivo\Form\Element\Select',
            'options' => array('label' => 'type'),
            'attributes' => array(
                'options' => array(
                    OverviewModel::TYPE_DYNAMIC => OverviewModel::TYPE_DYNAMIC,
                    OverviewModel::TYPE_STATIC => OverviewModel::TYPE_STATIC
                )
            )
        ));
        //TODO
//         $form->add(array(
//             'name' => 'overviewItems',
//             'type' => 'Vivo\Form\Element\Select',
//             'options' => array('label' => 'items'),
//             'attributes' => array('multiple' => true),
//         ));
        $form->add(array(
            'name' => 'overviewPath',
            'type' => 'Vivo\Form\Element\Text',
            'options' => array('label' => 'path'),
        ));
        $form->add(array(
            'name' => 'overviewSorting',
            'type' => 'Vivo\Form\Element\Select',
            'options' => array('label' => 'sorting'),
            'attributes' => array(
                'options' => $this->sortOptions
            )
        ));
        $form->add(array(
            'name' => 'overviewCriteria',
            'type' => 'Vivo\Form\Element\Text',
            'options' => array('label' => 'criteria'),
        ));
        $form->add(array(
            'name' => 'overviewLimit',
            'type' => 'Vivo\Form\Element\Text',
            'options' => array('label' => 'limit'),
        ));

        return $form;
    }

}
