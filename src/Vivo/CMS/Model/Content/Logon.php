<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * Logon
 * Logon form model
 */
class Logon extends Model\Content
{
    /**
     * Redirect to this URL after successful login
     * @var string
     */
    protected $logonUrl;

    /**
     * Redirect to this URL after logout
     * @var string
     */
    protected $logoffUrl;

    /**
     * Redirect to this URL when authentication fails
     * @var string
     */
    protected $errorUrl;

    /**
     * Sets URL to redirect to when authentication fails
     * @param string $errorUrl
     */
    public function setErrorUrl($errorUrl)
    {
        $this->errorUrl = $errorUrl;
    }

    /**
     * Returns URL to redirect to when authentication fails
     * @return string
     */
    public function getErrorUrl()
    {
        return $this->errorUrl;
    }

    /**
     * Sets URL to redirect to after logout
     * @param string $logoffUrl
     */
    public function setLogoffUrl($logoffUrl)
    {
        $this->logoffUrl = $logoffUrl;
    }

    /**
     * Returns URL to redirect to after logout
     * @return string
     */
    public function getLogoffUrl()
    {
        return $this->logoffUrl;
    }

    /**
     * Sets URL to redirect to after successful login
     * @param string $logonUrl
     */
    public function setLogonUrl($logonUrl)
    {
        $this->logonUrl = $logonUrl;
    }

    /**
     * Returns URL to redirect to after successful login
     * @return string
     */
    public function getLogonUrl()
    {
        return $this->logonUrl;
    }
}
