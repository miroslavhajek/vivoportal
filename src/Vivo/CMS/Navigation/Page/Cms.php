<?php
namespace Vivo\CMS\Navigation\Page;

use Vivo\CMS\Model\Document;

use Zend\Navigation\Page\AbstractPage;

/**
 * Class Cms
 * CMS navigation page
 * @package Vivo\CMS\Navigation\Page
 */
class Cms extends AbstractPage
{
    /**
     * Page (document) path relative to site
     * @var string
     */
    protected $sitePath;

    /**
     * Document model
     * @var Document
     */
    protected $document;

    /**
     * Is the page disabled?
     * @var bool
     */
    protected $disabled     = false;

    /**
     * Sets the document
     * @param \Vivo\CMS\Model\Document $document
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Returns the document
     * @return \Vivo\CMS\Model\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Sets the page (document) path relative to the site
     * @param string $sitePath
     */
    public function setSitePath($sitePath)
    {
        $this->sitePath = $sitePath;
    }

    /**
     * Returns the page (document) path relative to the site
     * @return string
     */
    public function getSitePath()
    {
        return $this->sitePath;
    }

    /**
     * Returns UUID of the page
     * @return null|string
     */
    public function getUuid()
    {
        if ($this->getDocument()) {
            $uuid   = $this->getDocument()->getUuid();
        } else {
            $uuid   = null;
        }
        return $uuid;
    }

    /**
     * Returns href for this page
     * @return string  the page's href
     */
    public function getHref()
    {
        return $this->sitePath;
    }

    /**
     * Sets if the page is disabled
     * @param boolean $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = (bool) $disabled;
    }

    /**
     * Returns if the page is disabled
     * @return boolean
     */
    public function getDisabled()
    {
        return $this->disabled;
    }
}
