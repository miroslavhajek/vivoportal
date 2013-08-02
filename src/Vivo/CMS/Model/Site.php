<?php
namespace Vivo\CMS\Model;

/**
 * Represents web site as VIVO model.
 */
class Site extends Folder
{
    /**
     * @var string Security domain name.
     */
    protected $domain;

    /**
     * @var string Parent site name.
     * @example META-SITE
     */
    protected $parentSite;

    /**
     * @var array Hosts are domain address under which you accessed the site.
     */
    protected $hosts = array();

    /**
     * @param string $domain Security domain name.
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Returns security domain name.
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param array $hosts
     */
    public function setHosts(array $hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * @return array
     */
    public function getHosts()
    {
        return $this->hosts;
    }
}

