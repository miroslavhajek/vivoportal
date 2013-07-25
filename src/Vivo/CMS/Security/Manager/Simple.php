<?php
namespace Vivo\CMS\Security\Manager;

use Vivo\Security\Principal;

use Zend\Session\SessionManager;

use stdClass;

/**
 * Simple
 * Simple security manager
 * For development purposes, implements just authentication
 */
class Simple extends AbstractManager
{
    /**
     * Credentials for successful authentication
     * @var array
     */
    protected $options  = array(
        'use_external_authentication'   => true,
        'security_domain'           => 'VIVO',
        'username'                  => 'vivo.user',
        'password'                  => 'password',
    );

    /**
     * Returns roles in security domain.
     * @param string $domain Domain name.
     * @return array
     */
    function getRoles($domain)
    {
        // TODO: Implement getRoles() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param string $rolename
     */
    function addRole($domain, $rolename)
    {
        // TODO: Implement addRole() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param string $rolename
     */
    function removeRole($domain, $rolename)
    {
        // TODO: Implement removeRole() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param string $rolename
     * @return array
     */
    function getRoleAccessRights($domain, $rolename)
    {
        // TODO: Implement getRoleAccessRights() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param string $rolename
     * @param string $access_right
     */
    function grantRoleAccessRight($domain, $rolename, $access_right)
    {
        // TODO: Implement grantRoleAccessRight() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param string $rolename
     * @param string $access_right
     */
    function revokeRoleAccessRight($domain, $rolename, $access_right)
    {
        // TODO: Implement revokeRoleAccessRight() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain Domain name.
     * @param string $pattern
     * @return array
     */
    function getUsers($domain, $pattern = false)
    {
        // TODO: Implement getUsers() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param string $username
     * @return stdClass
     */
    function getUser($domain, $username)
    {
        // TODO: Implement getUser() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param Principal\UserInterface $user
     */
    public function addUser(Principal\UserInterface $user)
    {
        // TODO: Implement addUser() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param Principal\UserInterface $user
     */
    public function updateUser(Principal\UserInterface $user)
    {
        // TODO: Implement updateUser() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param stdClass $user
     */
    function removeUser($domain, $user)
    {
        // TODO: Implement removeUser() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @return array
     */
    function getGroups($domain)
    {
        // TODO: Implement getGroups() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * Adds a security group
     * @param \Vivo\Security\Principal\GroupInterface $group
     */
    public function addGroup(Principal\GroupInterface $group)
    {
        // TODO: Implement addGroup() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param string $groupname
     */
    function removeGroup($domain, $groupname)
    {
        // TODO: Implement removeGroup() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param stdClass $user
     * @param string $groupname
     */
    function addUserToGroup($domain, $user, $groupname)
    {
        // TODO: Implement addUserToGroup() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param stdClass $user
     * @param string $groupname
     */
    function removeUserFromGroup($domain, $user, $groupname)
    {
        // TODO: Implement removeUserFromGroup() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain
     * @param string $username
     * @return array Array of stdClasses.
     */
    function getUserGroups($domain, $username)
    {
        // TODO: Implement getUserGroups() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain Security domain name.
     * @param string $username User login.
     * @return stdClass
     */
    function getUserProfile($domain, $username)
    {
        // TODO: Implement getUserProfile() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * @param string $domain Security domain name.
     * @param string $username User login.
     * @param stdClass $profile
     */
    function setUserProfile($domain, $username, $profile)
    {
        // TODO: Implement setUserProfile() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
    }

    /**
     * Authenticates a user
     * Returns user object upon successful authentication or null otherwise
     * @param string $domain
     * @param string $username
     * @param string $password
     * @return stdClass|null
     */
    function authenticate($domain, $username, $password)
    {
        if (strtolower($domain) == strtolower($this->options['security_domain'])
            && strtolower($username) == strtolower($this->options['username'])
            && $password == $this->options['password']) {
            //Authentication ok
            $user           = new Principal\User();
            $user->setDomain($this->options['security_domain']);
            $user->setUsername($this->options['username']);
            $user->setPasswordHash(md5($this->options['password']));
            $user->setActive(true);
        } else {
            //Authentication failed
            $user           = null;
        }
        $this->setUserPrincipal($user);
        return $user;
    }

    /**
     * Returns if the user is actually defined as a member of the specified group
     * @param string $domain
     * @param string $username
     * @param string $groupName
     * @return bool
     */
    protected function isUserInGroupReal($domain, $username, $groupName)
    {
        // TODO: Implement isUserInGroupReal() method.
    }
}