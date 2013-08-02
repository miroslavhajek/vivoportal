<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\CMS\Api;
use Vivo\CMS\Model;
use Vivo\UI\AbstractForm;
use Vivo\Form\Form;
use Vivo\Stdlib\Hydrator\EntityClassMethods as ClassMethodsHydrator;

class SiteMap extends AbstractForm implements EditorInterface
{
    /**
     * @var \Vivo\CMS\Model\Content\Overview
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
        //Origin
        $form->add(array(
            'name'      => 'origin',
            'type'      => 'Vivo\Form\Element\Text',
            'options'   => array(
                'label' => 'Origin document path',
                'description'   => 'Path of an entity which is the origin for the sitemap tree calculation. '
                                    . 'If not set, the current document is assumed as origin.',
            ),
        ));
        // Include root
        $form->add(array(
            'name'      => 'includeRoot',
            'type'      => 'Vivo\Form\Element\Checkbox',
            'options'   => array(
                'label' => 'Include root',
                'description'   => 'Should the root document be included in the navigation?',
            ),
        ));
        // 'showDescription' flag
        $form->add(array(
            'name'      => 'showDescription',
            'type'      => 'Vivo\Form\Element\Checkbox',
            'options'   => array(
                'label' => 'Show description',
                'description'   => 'Show description flag.'
                                    . 'If set, page description will be show in every node of the site map tree',
            ),
        ));
        return $form;
    }
}
