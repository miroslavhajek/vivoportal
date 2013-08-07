<?php
namespace Vivo\Security\Manager;

use Vivo\Security\Principal;

use Zend\Session;
use Zend\Stdlib\ArrayUtils;

/**
 * AbstractManager
 * Security Manager defines base methods for working with users, roles and rights in Vivo applications.
 * All new security managers must extends this class
 */
abstract class AbstractManager
{
    /* default users */
    const USER_ANONYMOUS        = 'anonymous';
    const USER_ADMINISTRATOR    = 'administrator';
    const USER_SYSTEM           = 'system';

    /* default user groups */
    const GROUP_ANYONE          = 'Anyone';

    /* default (builtin) security domain */
    const DOMAIN_VIVO           = 'VIVO';

    /**
     * @var Session\Container
     */
    protected $session;

    /**
     * Security manager options
     * @var array
     */
    protected $options  = array(
        'use_external_authentication'   => true,
    );

    /**
     * Constructor
     * @param Session\SessionManager $sessionManager
     * @param array $options
     */
    public function __construct(Session\SessionManager $sessionManager, array $options = array())
    {
        $this->session  = new Session\Container(__CLASS__, $sessionManager);
        $this->options  = ArrayUtils::merge($this->options, $options);
    }

    /**
     * Returns roles in security domain.
     * @param string $domain Domain name.
     * @return string[]
     */
    abstract public function getRoles($domain);

    /**
     * @param string $domain
     * @param string $rolename
     */
    abstract public function addRole($domain, $rolename);

    /**
     * @param string $domain
     * @param string $rolename
     */
    abstract public function removeRole($domain, $rolename);

    /**
     * @param string $domain
     * @param string $rolename
     * @return string[]
     */
    abstract public function getRoleAccessRights($domain, $rolename);

    /**
     * @param string $domain
     * @param string $rolename
     * @param string $access_right
     */
    abstract public function grantRoleAccessRight($domain, $rolename, $access_right);

    /**
     * @param string $domain
     * @param string $rolename
     * @param string $access_right
     */
    abstract public function revokeRoleAccessRight($domain, $rolename, $access_right);

    /**
     * @param string $domain Domain name.
     * @param string|bool $pattern
     * @return Principal\UserInterface[]
     */
    abstract public function getUsers($domain, $pattern = false);

    /**
     * @param string $domain
     * @param string $username
     * @return Principal\UserInterface
     */
    abstract public function getUser($domain, $username);

    /**
     * @param Principal\UserInterface $user
     */
    abstract public function addUser(Principal\UserInterface $user);

    /**
     * @param Principal\UserInterface $user
     */
    abstract public function updateUser(Principal\UserInterface $user);

    /**
     * Removes user
     * @param string $domain
     * @param string $username
     */
    abstract public function removeUser($domain, $username);

    /**
     * @param string $domain
     * @return Principal\GroupInterface[]
     */
    abstract public function getGroups($domain);

    /**
     * Adds a security group
     * @param \Vivo\Security\Principal\GroupInterface $group
     */
    abstract public function addGroup(Principal\GroupInterface $group);

    /**
     * @param string $domain
     * @param string $groupname
     */
    abstract public function removeGroup($domain, $groupname);

    /**
     * @param string $domain
     * @param string $username
     * @param string $groupname
     */
    abstract public function addUserToGroup($domain, $username, $groupname);

    /**
     * Removes user from group
     * @param string $domain
     * @param string $username
     * @param string $groupname
     */
    abstract public function removeUserFromGroup($domain, $username, $groupname);

    /**
     * @param string $domain
     * @param string $username
     * @return Principal\GroupInterface[]
     */
    abstract public function getUserGroups($domain, $username);

    /**
     * Returns if the user is a member of a group including default memberships (anyone, system, etc.)
     * @param string $domain Security domain name.
     * @param string $username User login.
     * @param string $groupName
     * @return bool
     */
    public function isUserInGroup($domain, $username, $groupName)
    {
        if (($groupName == self::GROUP_ANYONE) || ($username == self::USER_SYSTEM)) {
            $isInGroup  = true;
        } else {
            $isInGroup  = $this->isUserInGroupReal($domain, $username, $groupName);
        }
        return $isInGroup;
    }

    /**
     * Returns if the user is actually defined as a member of the specified group
     * @param string $domain
     * @param string $username
     * @param string $groupName
     * @return bool
     */
    abstract protected function isUserInGroupReal($domain, $username, $groupName);

    /**
     * Returns user principal of the currently logged-on client (backend) or site visitor
     * Returns null if no user is logged on
     * @return Principal\UserInterface|null
     */
    public function getUserPrincipal()
    {
        $principal = null;
        if (isset($this->session['security.principal'])
                && $this->session['security.principal'] instanceof Principal\UserInterface) {
            $principal = $this->session['security.principal'];
        }
        if (isset($this->options['use_external_authentication']) && $this->options['use_external_authentication']) {
            //TODO - refactor not to use $_SERVER
            if (!$principal && isset($_SERVER['REMOTE_USER']) && ($remoteUser = $_SERVER['REMOTE_USER'])) {
                $principal = new Principal\User();
                if ($pos = strpos($remoteUser, '@')) {
                    //Kerberos format
                    $principal->setDomain(substr($remoteUser, $pos + 1));
                    $principal->setUsername(strtolower(substr($remoteUser, 0, $pos)));
                } else {
                    //Winbind format
                    //Replace the standard winbind separator on Linux
                    $remoteUser = str_replace('+', '\\', $remoteUser);
                    $pos        = strpos($remoteUser, '\\');
                    if ($pos === false) {
                        //Format without domain
                        $principal->setUsername($remoteUser);
                    } else {
                        $principal->setDomain(substr($remoteUser, 0, $pos));
                        $principal->setUsername(strtolower(substr($remoteUser, $pos + 1)));
                    }
                }
                $this->setUserPrincipal($principal);
            }
        }
        return $principal;
    }

    /**
     * Sets user principal and returns it
     * @param Principal\UserInterface|null $principal
     * @return Principal\UserInterface|null
     */
    public function setUserPrincipal(Principal\UserInterface $principal = null)
    {
        if ($principal) {
            $this->session->getManager()->regenerateId();
        }
        $this->session['security.principal'] = $principal;
        return $principal;
    }

    /**
     * Removes user principal
     */
    public function removeUserPrincipal()
    {
        $this->setUserPrincipal(null);
    }

    /**
     * Returns security domain of the currently logged on user or the default security domain if no user is logged on
     * @return string
     */
    public function getPrincipalDomain()
    {
        $principal = $this->getUserPrincipal();
        if ($principal) {
            $domain = $principal->getDomain();
        } else {
            $domain = self::DOMAIN_VIVO;
        }
        return $domain;
    }

    /**
     * Returns username of the currently logged on user or username for anonymous user if no user is logged on
     * @return string
     */
    public function getPrincipalUsername()
    {
        $principal = $this->getUserPrincipal();
        if ($principal) {
            $username   = $principal->getUsername();
        } else {
            $username   = self::USER_ANONYMOUS;
        }
        return $username;
    }

    /**
     * Returns true if the current principal is a member of at least one of the groups
     * @param array $groupNames
     * @return bool
     */
    protected function isPrincipalMemberOf($groupNames)
    {
        return $this->isMemberOf($this->getPrincipalDomain(), $this->getPrincipalUsername(), $groupNames);
    }

    /**
     * Returns true if the specified user is a member of at least one of the groups
     * @param string $domain Security domain name
     * @param string $username User login
     * @param array $groupNames
     * @return bool
     */
    protected function isMemberOf($domain, $username, $groupNames)
    {
        if (!empty($groupNames)) {
            if (in_array($username, $groupNames) || in_array(self::GROUP_ANYONE, $groupNames)) {
                return true;
            }
            /** @var $group Principal\GroupInterface */
            foreach ($this->getGroups($domain) as $group) {
                foreach ($groupNames as $groupName) {
                    if (($groupName == $group->getGroupName())
                            && $this->isUserInGroup($domain, $username, $groupName)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

//    /**
//     * @param string $domain Security domain name.
//     * @param string $username User login.
//     * @return stdClass
//     */
//    abstract public function getUserProfile($domain, $username);

//    /**
//     * @param string $domain Security domain name.
//     * @param string $username User login.
//     * @param stdClass $profile
//     */
//    abstract public function setUserProfile($domain, $username, $profile);

    /**
     * Authenticates a user
     * Returns user object upon successful authentication or null otherwise
     * @param string $domain
     * @param string $username
     * @param string $password
     * @return Principal\UserInterface|null
     */
    abstract public function authenticate($domain, $username, $password);

    /**
     * Authenticates as system
     * Returns the principal representing the system
     * @return Principal\UserInterface
     */
    public function authenticateAsSystem()
    {
        $principal = new Principal\User();
        $principal->setDomain(self::DOMAIN_VIVO);
        $principal->setUsername(self::USER_SYSTEM);
        $this->setUserPrincipal($principal);
        return $principal;
    }

    //TODO - refactor to standard cache implementation
    /* L1 cache functions */

    /**
     * @var array L1 cache.
     */
    protected $cache = array();

    protected function &cache(/*$keys...*/)
    {
        $array = &$this->cache;
        $args = func_get_args();
        if (is_array($args[0]))
            $args = $args[0];
        foreach ($args as $key) {
            if (!array_key_exists($key, $array) || !is_array($array[$key]))
                $array[$key] = array();
            $array = &$array[$key];
        }
        return $array;
    }

    protected function flush(/*$keys...*/)
    {
        $array = &$this->cache(func_get_args());
        $array = array();
    }
}
