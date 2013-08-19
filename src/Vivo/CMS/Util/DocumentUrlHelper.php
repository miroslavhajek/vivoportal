<?php
namespace Vivo\CMS\Util;

use Vivo\CMS\Api;
use Vivo\CMS\Model;
use Vivo\Util\UrlHelper;

/**
 * View helper for getting document url
 */
class DocumentUrlHelper
{

    /**
     * @var Api\CMS
     */
    private $cmsApi;

    /**
     *
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * Constructor
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param UrlHelper $urlHelper
     */
    public function __construct(Api\CMS $cmsApi, UrlHelper $urlHelper)
    {
        $this->cmsApi = $cmsApi;
        $this->urlHelper = $urlHelper;
    }

    /**
     * Returns document url
     * @param \Vivo\CMS\Model\Folder $folder
     * @param array $options
     * @return string
     */
    public function getDocumentUrl(Model\Folder $document, array $options = array())
    {
        $entityUrl = $this->cmsApi->getEntityRelPath($document);
        $params  = array(
            'path' => $entityUrl,
        );
        // configure url helper
        if ($document instanceof Model\Document) {
            $options['settings']['secured'] = (bool) $document->getSecured();
        }

        return $this->urlHelper->fromRoute('vivo/cms', $params, $options);
    }
}
