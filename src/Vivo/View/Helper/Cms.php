<?php
namespace Vivo\View\Helper;

use Vivo\CMS\Event\CMSEvent;
use Vivo\CMS\Api\Document as DocumentApi;
use Vivo\CMS\Model\Content;

use Zend\View\Helper\AbstractHelper;

/**
 * Class Cms
 * @package Vivo\View\Helper
 */
class Cms extends AbstractHelper
{
    /**
     * CMS Event
     * @var CMSEvent
     */
    protected $cmsEvent;

    /**
     * Document API
     * @var DocumentApi
     */
    protected $documentApi;

    /**
     * Constructor
     * @param CMSEvent $cmsEvent
     * @param \Vivo\CMS\Api\Document $documentApi
     */
    public function __construct(CMSEvent $cmsEvent, DocumentApi $documentApi)
    {
        $this->cmsEvent     = $cmsEvent;
        $this->documentApi  = $documentApi;
    }

    /**
     * Invoke the helper as a PhpRenderer method call
     * @param string|null $quickCmd
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function __invoke($quickCmd = null)
    {
        if (is_null($quickCmd)) {
            return $this;
        }
        switch ($quickCmd) {
            case 'requestedDocument':
                $retVal = $this->getRequestedDocument();
                break;
            case 'site':
                $retVal = $this->getSite();
                break;
            case 'requestedPath':
                $retVal = $this->getRequestedPath();
                break;
            default:
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: Unsupported quick command '%s'", __METHOD__, $quickCmd));
                break;
        }
        return $retVal;
    }

    /**
     * Returns requested document
     * @return \Vivo\CMS\Model\Document
     */
    public function getRequestedDocument()
    {
        return $this->cmsEvent->getDocument();
    }

    /**
     * Returns current site
     * @return \Vivo\CMS\Model\Site
     */
    public function getSite()
    {
        return $this->cmsEvent->getSite();
    }

    /**
     * Returns the requested path
     * @return string
     */
    public function getRequestedPath()
    {
        return $this->cmsEvent->getRequestedPath();
    }

    /**
     * Returns document for the given content
     * @param Content $content
     * @return \Vivo\CMS\Model\Document
     */
    public function getContentDocument(Content $content)
    {
        $document   = $this->documentApi->getContentDocument($content);
        return $document;
    }
}
