<?php
namespace Vivo\CMS\Model\Content;

use Vivo\CMS\Model;

/**
 * Logon
 * Logon form model
 */
class Logon extends Model\Content implements Model\SymRefDataExchangeInterface
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

    /**
     * Exchange internal values containing symbolic refs / URLs from provided array
     * @param  array $data
     * @return void
     */
    public function exchangeArraySymRef(array $data)
    {
        //Error URL
        if (array_key_exists('error_url', $data)) {
            $this->setErrorUrl($data['error_url']);
        }
        //Logoff URL
        if (array_key_exists('logoff_url', $data)) {
            $this->setLogoffUrl($data['logoff_url']);
        }
        //Logon URL
        if (array_key_exists('logon_url', $data)) {
            $this->setLogonUrl($data['logon_url']);
        }
    }

    /**
     * Return an array representation of the object's properties containing symbolic refs / URLs
     * @return array
     */
    public function getArrayCopySymRef()
    {
        return array(
            'error_url'  => $this->getErrorUrl(),
            'logoff_url' => $this->getLogoffUrl(),
            'logon_url'  => $this->getLogonUrl(),
        );
    }
}
