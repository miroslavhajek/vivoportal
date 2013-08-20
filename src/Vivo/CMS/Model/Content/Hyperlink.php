<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * Content to redirects to the URL.
 * @todo recursive property?
 */
class Hyperlink extends Model\Content
{

    /**
     * Hyperlink url.
     * @var string
     */
    protected $url;

    /**
     * Returns hyperlink url.
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets hyperlink url.
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
