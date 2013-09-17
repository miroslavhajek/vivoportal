<?php
namespace Vivo\CMS\UI\Content\Editor;

use Vivo\CMS\Api;
use Vivo\CMS\Model;
use Vivo\UI\AbstractForm;
use Vivo\UI\ComponentEventInterface;
use Vivo\Form\Form;
use Vivo\Stdlib\Hydrator\EntityClassMethods as ClassMethodsHydrator;

class Editor extends AbstractForm implements EditorInterface
{
    /**
     * @var \Vivo\CMS\Model\Content
     */
    private $content;

    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;

    /**
     * @param \Vivo\CMS\Api\Document $documentApi
     */
    public function __construct(Api\Document $documentApi)
    {
        $this->documentApi = $documentApi;
    }

    /**
     * @see \Vivo\CMS\UI\Content\Editor\EditorInterface::setContent()
     */
    public function setContent(Model\Content $content)
    {
        $this->content = $content;
    }

    public function attachListeners()
    {
        parent::attachListeners();

        $this->getEventManager()
             ->attach(ComponentEventInterface::EVENT_INIT, array($this, 'initListenerFormBindContent'));
    }

    public function initListenerFormBindContent()
    {
        $this->getForm()->bind($this->content);
    }

    /**
     * @see \Vivo\CMS\UI\Content\Editor\EditorInterface::save()
     */
    public function save(Model\ContentContainer $container)
    {
        if($this->getForm()->isValid()) {
            if($this->content->getUuid()) {
                $this->documentApi->saveContent($this->content);
            }
            else {
                $this->documentApi->createContent($container, $this->content);
            }
        }
    }

    /**
     * @see \Vivo\UI\AbstractForm::doGetForm()
     */
    public function doGetForm()
    {
        $form = new Form('editor-'.$this->content->getUuid());
        $form->setWrapElements(true);
        $form->setHydrator(new ClassMethodsHydrator(false));
        $form->setOptions(array('use_as_base_fieldset' => true));

        return $form;
    }
}
