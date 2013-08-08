<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * Content to redirects to the URL.
 * @todo recursive property?
 */
class Hyperlink extends Model\Content implements Model\SymRefDataExchangeInterface
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

    /**
     * Exchange internal values containing symbolic refs / URLs from provided array
     * @param  array $data
     * @return void
     */
    public function exchangeArraySymRef(array $data)
    {
        if (array_key_exists('url', $data)) {
            $this->setUrl($data['url']);
        }
    }

    /**
     * Return an array representation of the object's properties containing symbolic refs / URLs
     * @return array
     */
    public function getArrayCopySymRef()
    {
        return array('url' => $this->getUrl());
    }
}
