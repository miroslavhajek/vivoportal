<?php
namespace Vivo\View\Helper;

use Vivo\Security\Principal\UserInterface;

use Zend\View\Helper\AbstractHelper;
use Zend\Stdlib\ArrayUtils;

/**
 * User
 * Provides access to user principal info
 */
class User extends AbstractHelper
{
    /**
     * User principal
     * @var UserInterface
     */
    protected $userPrincipal;

    /**
     * Options
     * @var array
     */
    protected $options = array(
        //DateTime formatting string passed to \DateTime::format()
        'dateTimeFormat'    => 'Y-m-d H:i:s',
        'messages'  => array(
            //When user principal is not set, this string will be displayed in place of the actual values
            'notLogged'     => 'User not logged in',
            //Returned as expiration string when expiration is not set
            'noExpiration'  => 'Never',
            //Returned as security domain when domain is not set
            'domainNotSet'  => '',
            //Returned as e-mail when e-mail is not set
            'emailNotSet'   => '',
        ),
    );

    /**
     * Constructor
     * @param UserInterface $userPrincipal
     * @param array $options
     */
    public function __construct(UserInterface $userPrincipal = null, array $options = array())
    {
        $this->userPrincipal    = $userPrincipal;
        $this->options          = ArrayUtils::merge($this->options, $options);
    }

    /**
     * Invoke the helper as a PhpRenderer method call
     * @param string $quickCmd
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function __invoke($quickCmd = null)
    {
        if (is_null($quickCmd)) {
            return $this;
        }
        switch ($quickCmd) {
            case 'domain':
                $retVal = $this->getDomain();
                break;
            case 'email':
                $retVal = $this->getEmail();
                break;
            case 'expiration':
                $retVal = $this->getExpiration();
                break;
            case 'fullname':
                $retVal = $this->getFullName();
                break;
            case 'name':
                $retVal = $this->getName();
                break;
            case 'username':
                $retVal = $this->getUsername();
                break;
            case 'principal':
                $retVal = $this->getUserPrincipal();
                break;
            default:
                throw new Exception\InvalidArgumentException(
                    sprintf("%s: Unsupported quick command '%s'", __METHOD__, $quickCmd));
                break;
        }
        return $retVal;
    }

    /**
     * Returns if the user principal is set (i.e. a user is logged in)
     * @return bool
     */
    public function hasUserPrincipal()
    {
        return !is_null($this->userPrincipal);
    }

    /**
     * Returns user security domain
     * @return string
     */
    public function getDomain()
    {
        if ($this->hasUserPrincipal()) {
            $retVal = $this->userPrincipal->getDomain();
            if (is_null($retVal)) {
                $retVal = $this->options['messages']['domainNotSet'];
            }
        } else {
            $retVal = $this->options['messages']['notLogged'];
        }
        return $retVal;
    }

    /**
     * Returns user's e-mail
     * @return string
     */
    public function getEmail()
    {
        if ($this->hasUserPrincipal()) {
            $retVal = $this->userPrincipal->getEmail();
            if (is_null($retVal)) {
                $retVal = $this->options['messages']['emailNotSet'];
            }
        } else {
            $retVal = $this->options['messages']['notLogged'];
        }
        return $retVal;
    }

    /**
     * Returns formatted expiration of the user account
     * @return string
     */
    public function getExpiration()
    {
        if ($this->hasUserPrincipal()) {
            $expiration = $this->userPrincipal->getExpiration();
            if (is_null($expiration)) {
                $retVal = $this->options['messages']['noExpiration'];
            } else {
                $retVal = $expiration->format($this->options['dateTimeFormat']);
            }
        } else {
            $retVal = $this->options['messages']['notLogged'];
        }
        return $retVal;
    }

    /**
     * Returns user's full name
     * @return string
     */
    public function getFullName()
    {
        if ($this->hasUserPrincipal()) {
            $retVal = $this->userPrincipal->getFullName();
        } else {
            $retVal = $this->options['messages']['notLogged'];
        }
        return $retVal;
    }

    /**
     * Returns user principal name
     * @return string
     */
    public function getName()
    {
        if ($this->hasUserPrincipal()) {
            $retVal = $this->userPrincipal->getName();
        } else {
            $retVal = $this->options['messages']['notLogged'];
        }
        return $retVal;
    }

    /**
     * Returns user name
     * @return string
     */
    public function getUsername()
    {
        if ($this->hasUserPrincipal()) {
            $retVal = $this->userPrincipal->getUsername();
        } else {
            $retVal = $this->options['messages']['notLogged'];
        }
        return $retVal;
    }

    /**
     * Returns user principal or null when principal is not set
     * @return UserInterface|null
     */
    public function getUserPrincipal()
    {
        return $this->userPrincipal;
    }
}
