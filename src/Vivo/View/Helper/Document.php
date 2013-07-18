<?php
namespace Vivo\View\Helper;

use Vivo\CMS\Api;
use Vivo\CMS\Model;

use Zend\View\Helper\AbstractHelper;

/**
 * View helper for getting document url
 */
class Document extends AbstractHelper
{
    /**
     * Helper options
     * @var array
     */
    private $options = array();

    /**
     * @var Api\CMS
     */
    private $cmsApi;

    /**
     * @param \Vivo\CMS\Api\CMS $cmsApi
     * @param array $options
     */
    public function __construct(Api\CMS $cmsApi, $options = array())
    {
        $this->cmsApi = $cmsApi;
        $this->options = array_merge($this->options, $options);
    }

    public function __invoke(Model\Document $document)
    {
        $entityUrl = $this->cmsApi->getEntityRelPath($document);
        $urlHelper = $this->getView()->plugin('url');
        $urlParams  = array(
            'path' => $entityUrl,
        );
        $options    = array();
        $url = $urlHelper('vivo/cms', $urlParams, $options, false);

        //Replace encoded slashes in the url. It's needed because apache
        //returns 404 when the url contains encoded slashes. This behaviour
        //could be changed in apache config, but it is not possible to do that
        //in .htaccess context.
        //@see http://httpd.apache.org/docs/current/mod/core.html#allowencodedslashes
        return str_replace('%2F', '/', $url);
    }
}
