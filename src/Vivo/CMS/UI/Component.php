<?php
namespace Vivo\CMS\UI;

use Vivo\CMS\Model\Content;
use Vivo\CMS\Model\Document;
use Vivo\UI\ComponentContainer;
use Vivo\CMS\Event\CMSEvent;
use Vivo\Service\Initializer\CmsEventAwareInterface;

/**
 * Base component for CMS UI components.
 */
class Component extends ComponentContainer implements InjectModelInterface, CmsEventAwareInterface
{

    /**
     * @var Document
     */
    protected $document;

    /**
     * @var Content
     */
    protected $content;

    /**
     * CMS Event
     * @var CMSEvent
     */
    protected $cmsEvent;

    /**
     * @param Document $document
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;
    }

    /**
     * @param Content $content
     */
    public function setContent(Content $content)
    {
        $this->content = $content;
    }

    public function view() {
        $view = parent::view();
        $view->content  = $this->content;
        $view->document = $this->document;

        return $view;
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
}
