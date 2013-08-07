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
     * @var array HTML attributes.
     */
    protected $bodyAttributes = array();

    /**
     * @param string Path to entity.
     * @param Vivo\CMS\Model\Entity\Security
     */
    public function __construct($path = null, $security = null)
    {
        parent::__construct($path, $security);
    }

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

    /**
     * Sets HTML body attributes
     * @param array $bodyAttributes
     */
    public function setBodyAttributes(array $bodyAttributes)
    {
        $this->bodyAttributes = $bodyAttributes;
    }

    /**
     * Returns HTML body attributes
     * @return array
     */
    public function getBodyAttributes()
    {
        return $this->bodyAttributes;
    }
}

