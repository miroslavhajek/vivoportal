<?php
namespace Vivo\CMS\UI;

use Vivo\CMS\Api;
use Vivo\Http\StreamResponse;

class Rss extends Component
{
    /**
     * @var \Vivo\CMS\Api\Document
     */
    private $documentApi;

    /**
     * @var Vivo\Http\StreamResponse
     */
    private $response;

    private $items = array();

    /**
     * @param \Vivo\CMS\Api\Document $documentApi
     * @param \Vivo\Http\StreamResponse $response
     */
    public function __construct(Api\Document $documentApi, StreamResponse $response)
    {
        $this->documentApi = $documentApi;
        $this->response = $response;
    }

    public function init()
    {
        foreach ($this->documentApi->getChildDocuments($this->document) as $child) {
            if($this->documentApi->isPublished($child)) {
                $this->items[] = $child;
            }
        }
    }

    public function view()
    {
        $this->response->getHeaders()->addHeaderLine('Content-Type', 'text/xml; charset:utf-8');

        $view = parent::view();
        $view->site = $this->cmsEvent->getSite();
        $view->items = $this->items;

        return $view;
    }
}
