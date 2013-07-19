<?php
namespace Vivo\View\Helper;

use Vivo\CMS\Model;
use Vivo\CMS\Util\DocumentUrlHelper;

use Zend\View\Helper\AbstractHelper;

/**
 * View helper for getting document url
 */
class Document extends AbstractHelper
{
    /**
     * Document url helper
     * @var DocumentUrlHelper
     */
    private $documentUrlHelper;

    /**
     * Constructor
     * @param \Vivo\CMS\Api\CMS $cmsApi
     */
    public function __construct(DocumentUrlHelper $documentUrlHelper)
    {
        $this->documentUrlHelper = $documentUrlHelper;
    }

    /**
     * Returns document url
     * @param \Vivo\CMS\Model\Document $document
     * @return string
     */
    public function __invoke(Model\Document $document)
    {
        return $this->documentUrlHelper->getDocumentUrl($document);
    }
}
