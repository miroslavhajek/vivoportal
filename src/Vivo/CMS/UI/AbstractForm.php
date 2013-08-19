<?php
namespace Vivo\CMS\UI;

use Vivo\UI\AbstractForm as AbstractVivoForm;
use Vivo\CMS\Model\Content;
use Vivo\CMS\Model\Document;
use Vivo\CMS\Event\CMSEvent;
use Vivo\Service\Initializer\CmsEventAwareInterface;
use Vivo\UI\ComponentEventInterface;

use Zend\Form\FormInterface;

/**
 * AbstractForm
 * Abstract CMS Form
 */
abstract class AbstractForm extends AbstractVivoForm implements InjectModelInterface,
                                                                CmsEventAwareInterface
{
    /**
     * @var Content
     */
    protected $content;

    /**
     * @var Document
     */
    protected $document;

    /**
     * CMS Event
     * @var CMSEvent
     */
    protected $cmsEvent;

    /**
     * Sets content
     * @param Content $content
     * @return void
     */
    public function setContent(Content $content)
    {
        $this->content  = $content;
    }

    /**
     * Sets document
     * @param Document $document
     * @return void
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Sets the CMS event
     * @param CMSEvent $cmsEvent
     * @return void
     */
    public function setCmsEvent(CMSEvent $cmsEvent)
    {
        $this->cmsEvent = $cmsEvent;
    }

    /**
     * View listener
     */
    public function viewListenerSetContentAndDocument() {
        $this->view->content           = $this->content;
        $this->view->document          = $this->document;
    }

    /**
     * Attaches listeners
     * @return void
     */
    public function attachListeners()
    {
        parent::attachListeners();
        $eventManager   = $this->getEventManager();
        //View
        $eventManager->attach(ComponentEventInterface::EVENT_VIEW, array($this, 'viewListenerSetContentAndDocument'));
    }

}
