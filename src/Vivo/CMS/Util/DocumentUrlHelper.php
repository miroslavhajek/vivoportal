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
     * @param array $options
     */
    public function __construct(Api\CMS $cmsApi, UrlHelper $urlHelper)
    {
        $this->cmsApi = $cmsApi;
        $this->urlHelper = $urlHelper;
    }

    /**
     * Returns document url
     * @param \Vivo\CMS\Model\Document $document
     * @return string
     */
    public function getDocumentUrl(Model\Document $document)
    {
        $entityUrl = $this->cmsApi->getEntityRelPath($document);
        $urlParams  = array(
            'path' => $entityUrl,
        );
        $options    = array();
        $url = $this->urlHelper->fromRoute('vivo/cms', $urlParams, $options, false);

        //Replace encoded slashes in the url. It's needed because apache
        //returns 404 when the url contains encoded slashes. This behaviour
        //could be changed in apache config, but it is not possible to do that
        //in .htaccess context.
        //@see http://httpd.apache.org/docs/current/mod/core.html#allowencodedslashes
        return str_replace('%2F', '/', $url);
    }
}
