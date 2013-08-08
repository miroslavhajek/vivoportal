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
     * @param DocumentUrlHelper $documentUrlHelper
     */
    public function __construct(DocumentUrlHelper $documentUrlHelper)
    {
        $this->documentUrlHelper = $documentUrlHelper;
    }

    /**
     * Returns document url
     * @param \Vivo\CMS\Model\Folder $folder
     * @param array $options
     * @return string
     */
    public function __invoke(Model\Folder $document, array $options = array())
    {
        return $this->documentUrlHelper->getDocumentUrl($document, $options);
    }
}
