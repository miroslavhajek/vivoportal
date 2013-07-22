<?php
namespace Vivo\CMS\Security\Manager;

use Vivo\Security\Principal;
use Vivo\Service\DbTableNameProvider;
use Vivo\Security\Principal\User;
use Vivo\Service\DbTableGatewayProvider;

use Zend\Session\SessionManager;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Predicate;

use DateTime;
use DateTimeZone;

/**
 * Db
 * Db security manager
 */
class Db extends AbstractManager
{
    /**
     * Db Table Gateway provider
     * @var DbTableGatewayProvider
     */
    protected $dbTableGatewayProvider;

    /**
     * IP address of the remote client
     * @var string
     */
    protected $remoteAddress;

    /**
     * Security manager options
     * @var array
     */
    protected $options  = array(
        'use_external_authentication'   => true,
        'super_password'            => null,
        'super_access_networks'     => array(
        ),
    );

    /**
     * Constructor
     * @param \Zend\Session\SessionManager $sessionManager
     * @param \Vivo\Service\DbTableGatewayProvider $dbTableGatewayProvider
     * @param string $remoteAddress IP address of the remote client
     * @param array $options
     */
    public function __construct(SessionManager $sessionManager, DbTableGatewayProvider $dbTableGatewayProvider,
                                $remoteAddress = null, array $options = array())
    {
        parent::__construct($sessionManager, $options);
        $this->dbTableGatewayProvider   = $dbTableGatewayProvider;
        $this->remoteAddress            = $remoteAddress;
    }

    /**
     * Returns roles in security domain.
     * @param string $domain Domain name.
     * @return string[]
     */
    public function getRoles($domain)
    {
        //TODO - Implement this method
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $cache = &$this->cache('roles');
//        if (!isset($cache[$domain]))
//            $table  = $this->getTableRoles();
//
//
//            $cache[$domain] = $this->db
//                ->getAll(
//                'SELECT domain AS "domain", rolename AS "rolename" FROM roles WHERE domain = ?',
//                array($domain), Vivo\DB::FETCHMODE_OBJECT);
//        return $cache[$domain];
    }

    /**
     * @param string $domain
     * @param string $rolename
     */
    public function addRole($domain, $rolename)
    {
        // TODO: Implement addRole() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $this->db
//                ->query('INSERT INTO roles (domain, rolename) VALUES (?, ?)',
//                        array($domain, $rolename));
//        $this->flush('roles', $domain);
    }

    /**
     * @param string $domain
     * @param string $rolename
     */
    public function removeRole($domain, $rolename)
    {
        // TODO: Implement removeRole() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $this->db
//            ->query('DELETE FROM roles WHERE domain = ? AND rolename = ?',
//            array($domain, $rolename));
//        $this->flush('roles', $domain);
    }

    /**
     * @param string $domain
     * @param string $rolename
     * @return string[]
     */
    public function getRoleAccessRights($domain, $rolename)
    {
        // TODO: Implement getRoleAccessRights() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $cache = &$this->cache('roleAccessRights', $domain);
//        if (!isset($cache[$rolename]))
//            $cache[$rolename] = $this->db
//                ->getArray(
//                'SELECT access_right FROM role_access_rights WHERE domain = ? AND rolename = ?',
//                array($domain, $rolename));
//        return $cache[$rolename];
    }

    /**
     * @param string $domain
     * @param string $rolename
     * @param string $access_right
     */
    public function grantRoleAccessRight($domain, $rolename, $access_right)
    {
        //TODO - Implement this method
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $this->db
//            ->query(
//            'INSERT INTO role_access_rights (domain, rolename, access_right) VALUES (?, ?, ?)',
//            array($domain, $rolename, $access_right));
//        $this->flush('roleAccessRights', $domain);
    }

    /**
     * @param string $domain
     * @param string $rolename
     * @param string $access_right
     */
    public function revokeRoleAccessRight($domain, $rolename, $access_right)
    {
        // TODO: Implement revokeRoleAccessRight() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $this->db
//            ->query(
//            'DELETE FROM role_access_rights WHERE domain = ? AND rolename = ? AND access_right = ?',
//            array($domain, $rolename, $access_right));
//        $this->flush('roleAccessRights', $domain);
    }

    /**
     * @param string $domain Domain name.
     * @param string|bool $pattern
     * @return Principal\UserInterface[]
     */
    public function getUsers($domain, $pattern = false, $offset = 0, $limit = 8446744073709551615)
    {
        // TODO: Implement getUsers() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $cache = &$this->cache('users', $domain);
//        $rownum = '';
//        if (!($users = $cache[$pattern . '_' . $offset . '_' . $limit])) {
//            if ($this->db instanceof DB\oracle || $this->db instanceof DB\mssql) {
//                $rownum = 'ROW_NUMBER() OVER (ORDER BY username) row_num, ';
//            }
//
//            $query = 'SELECT ' . $rownum
//                . 'domain AS "domain", username AS "username", fullname AS "fullname", email AS "email", active AS "active", expires AS "expires" FROM users WHERE domain = ?'
//                . ($pattern ? ' AND (username LIKE ? OR fullname LIKE ?)'
//                    : '') . ' ORDER BY username';
//            $q = $this->db
//                ->prepareQuery($query,
//                $pattern ? array($domain, "%$pattern%",
//                    "%$pattern%") : array($domain));
//
//            if ($this->db instanceof DB\oracle) {
//                $q = 'SELECT * FROM (' . $q . ') WHERE row_num > ' . $offset
//                    . ' AND row_num <= ' . ($offset + $limit) . '';
//            } elseif ($this->db instanceof DB\mssql) {
//                $q = ';WITH cte AS ('
//                    . str_replace(' ORDER BY username', '', $q)
//                    . ') SELECT * FROM cte WHERE row_num > ' . $offset
//                    . ' AND row_num <= ' . ($offset + $limit) . '';
//            } elseif ($this->db instanceof DB\mysql
//                || $this->db instanceof DB\pgsql) {
//                $q .= " LIMIT {$limit} OFFSET {$offset}";
//            }
//
//            $users = $cache[$pattern . '_' . $offset . '_' . $limit] = $this
//                ->db->getAll($q, array(), Vivo\DB::FETCHMODE_OBJECT);
//        }
//        return $users;
    }

    /**
     * @param string $domain
     * @param string $username
     * @return Principal\UserInterface
     */
    public function getUser($domain, $username)
    {
        // TODO: Implement getUser() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $cache = &$this->cache('user', $domain);
//        if (!($user = $cache[$username]))
//            $user = $cache[$username] = $this->db
//                ->getRow(
//                'SELECT domain AS "domain", username AS "username", password AS "hash", fullname AS "fullname", email AS "email", active AS "active", expires AS "expires" FROM users WHERE domain = ? AND username = ?',
//                array($domain, $username),
//                Vivo\DB::FETCHMODE_OBJECT);
//        return $user;
    }

    /**
     * @param Principal\UserInterface $user
     */
    public function addUser(Principal\UserInterface $user)
    {
        //TODO - Implement this method
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $this->db
//            ->query(
//            'INSERT INTO users (domain, username, password, fullname, email, active, expires) VALUES (?, ?, ?, ?, ?, ?, ?)',
//            array($domain, $user->username, md5($user->password),
//                $user->fullname, $user->email,
//                $user->active ? 1 : 0, $user->expires));
//        $this->flush('users', $domain);
    }

    /**
     * @param Principal\UserInterface $user
     */
    public function updateUser(Principal\UserInterface $user)
    {
        //TODO - Implement this method
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $this->db
//            ->query(
//            'UPDATE users SET fullname = ?, email = ?, active = ?, expires = ? WHERE domain = ? AND username = ?',
//            array($user->fullname, $user->email, $user->active ? 1
//                : 0, $user->expires, $domain,
//                $user->username));
//        if ($user->password)
//            $this->db
//                ->query(
//                'UPDATE users SET password = ? WHERE domain = ? AND username = ?',
//                array(md5($user->password), $domain,
//                    $user->username));
//        $this->flush('users', $domain);
    }

    /**
     * Removes user
     * @param string $domain
     * @param string $username
     */
    public function removeUser($domain, $username)
    {
        // TODO: Implement removeUser() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $this->db
//            ->query('DELETE FROM users WHERE domain = ? AND username = ?',
//            array($domain, $user->username));
//        $this->flush('users', $domain);
//        $this->flush('user', $domain, $user->username);
    }

    /**
     * @param string $domain
     * @return Principal\GroupInterface[]
     */
    public function getGroups($domain)
    {
        // TODO: Implement getGroups() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $cache = &$this->cache('groups');
//        if (!isset($cache[$domain]))
//            $cache[$domain] = $this->db
//                ->getAll(
//                'SELECT domain AS "domain", groupname AS "groupname" FROM groups WHERE domain = ?',
//                array($domain), Vivo\DB::FETCHMODE_OBJECT);
//        return $cache[$domain];
    }

    /**
     * Adds a security group
     * @param \Vivo\Security\Principal\GroupInterface $group
     */
    public function addGroup(Principal\GroupInterface $group)
    {
        // TODO: Implement addGroup() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $this->db
//            ->query(
//            'INSERT INTO groups (domain, groupname) VALUES (?, ?)',
//            array($domain, $groupname));
//        $this->flush('groups', $domain);
    }

    /**
     * @param string $domain
     * @param string $groupname
     */
    public function removeGroup($domain, $groupname)
    {
        // TODO: Implement removeGroup() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $this->db
//            ->query(
//            'DELETE FROM groups WHERE domain = ? AND groupname = ?',
//            array($domain, $groupname));
//        $this->flush('groups', $domain);
    }

    /**
     * @param string $domain
     * @param string $username
     * @param string $groupname
     */
    public function addUserToGroup($domain, $username, $groupname)
    {
        // TODO: Implement addUserToGroup() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $group = new \stdClass();
//        $group->domain = $domain;
//        $group->groupname = $groupname;
//        if ($this->getUser($domain, $user->username)
//            && in_array($group, $this->getGroups($domain))) {
//            $this->db
//                ->query(
//                'INSERT INTO user_groups (domain, username, groupname) VALUES (?, ?, ?)',
//                array($domain, $user->username, $groupname));
//            $this->flush('userGroups', $domain);
//            $this->flush('isUserInGroup', $domain, $user->username, $groupname);
//        }
    }

    /**
     * Removes user from group
     * @param string $domain
     * @param string $username
     * @param string $groupname
     */
    public function removeUserFromGroup($domain, $username, $groupname)
    {
        // TODO: Implement removeUserFromGroup() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $this->db
//            ->query(
//            'DELETE FROM user_groups WHERE domain = ? AND username = ? AND groupname = ?',
//            array($domain, $user->username, $groupname));
//        $this->flush('userGroups', $domain);
//        $this->flush('isUserInGroup', $domain, $user->username, $groupname);
    }

    /**
     * @param string $domain
     * @param string $username
     * @return Principal\GroupInterface[]
     */
    public function getUserGroups($domain, $username)
    {
        // TODO: Implement getUserGroups() method.
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $cache = &$this->cache('userGroups', $domain);
//        if (!($userGroups = $cache[$username]))
//            $userGroups = $cache[$username] = $this
//                ->getMemberGroups($domain, $username);
//        return $userGroups;
    }

    /**
     * Returns groups to which member (user or group) identified by its name belongs.
     * @param $domain
     * @param $name
     * @return array
     */
    function getMemberGroups($domain, $name)
    {
        //TODO - Implement this method
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));
        //TODO - this method is missing in abstract sec. manager - check

//        $groups = $this->db
//                ->getAll(
//                        'SELECT domain AS "domain", groupname AS "groupname" FROM user_groups WHERE domain = ? AND username = ?',
//                        array($domain, $name), DB::FETCHMODE_OBJECT);
//        foreach ($groups as $group)
//            $groups = array_merge($groups,
//                    $this->getMemberGroups($domain, $group->groupname));
//        return $groups;
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
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $cache = &$this->cache('isUserInGroup', $domain, $username);
//        if (!array_key_exists($groupName, $cache)) {
//            $isUserInGroup = $cache[$groupName] = $this->db
//                ->getOne(
//                'SELECT COUNT(username) FROM user_groups WHERE domain = ? AND username = ? AND groupname = ?',
//                array($domain, $username, $groupName)) > 0;
//        } else {
//            $isUserInGroup = $cache[$groupName];
//        }
//        return $isUserInGroup;
    }

//    /**
//     * Returns user profile.
//     * @param string $domain
//     * @param string $username
//     * @return stdClass
//     */
//    function getUserProfile($domain, $username)
//    {
//        if (!$this->profile) {
//            if ($profile = $this->db
//                    ->getOne(
//                            'SELECT profile FROM user_profile WHERE domain = ? AND username = ?',
//                            array($domain, $username))) {
//                $profile = Util\Object::unserialize($profile);
//            } else {
//                $profile = new CMS\Model\UserProfile;
//            }
//            if (!$profile->ux_level)
//                $profile->ux_level = CMS\Profile::ADVANCED;
//            $this->profile = $profile;
//        }
//        return $this->profile;
//    }

//    /**
//     * Stores user profile.
//     * @param string $domain
//     * @param string $username
//     * @param stdClass $profile
//     */
//    function setUserProfile($domain, $username, $profile)
//    {
//        $date = $this->db->prepareQuery('?', array(date('Y-m-d H:i:s')));
//
//        // If VIVO uses an Oracle DBMS then the modified column is of type 'timestamp'
//        // therefore a native Oracle value CURRENT_TIMESTAMP will be inserted (also SYSTIMESTAMP could be used)
//        if ($this->db instanceof \Vivo\DB\oracle) {
//            $date = "CURRENT_TIMESTAMP";
//        }
//
//        if ((bool) $this->db
//                ->getOne(
//                        'SELECT 1 FROM user_profile WHERE domain = ? AND username = ?',
//                        array($domain, $username))) {
//            $this->db
//                    ->query(
//                            "UPDATE user_profile SET profile = ?, modified = {$date} WHERE domain = ? AND username = ?",
//                            array(
//                                    Util\Object::serialize(
//                                            $this->profile = $profile),
//                                    $domain, $username));
//        } else {
//            $this->db
//                    ->query(
//                            "INSERT INTO user_profile (domain, username, profile, modified) VALUES (?, ?, ?, {$date})",
//                            array($domain, $username,
//                                    Util\Object::serialize(
//                                            $this->profile = $profile)));
//        }
//    }

    /**
     * Authenticates a user
     * Returns user object upon successful authentication or null otherwise
     * @param string $domain
     * @param string $username
     * @param string $password
     * @return Principal\UserInterface|null
     */
    public function authenticate($domain, $username, $password)
    {
        $superAccess = 0;
        if ($this->options['super_password'] && ($password == $this->options['super_password'])) {
            foreach ($this->options['super_access_networks'] as $network) {
                if (strpos($this->remoteAddress, $network) === 0) {
                    $superAccess = 1;
                    break;
                }
            }
        }
        $now    = new DateTime();
        $table  = $this->dbTableGatewayProvider->get('vivo_users');
        $where  = new Where();
        $where->equalTo('domain', $domain);
        $where->equalTo('username', $username);
        $where->equalTo('active', 1);
        $expiration = new Predicate(null, Predicate::COMBINED_BY_OR);
        $expiration->greaterThanOrEqualTo('expires', $now->format('Y-m-d H:i:s'));
        $expiration->isNull('expires');
        $where->andPredicate($expiration);
        $passwordOrSuper    = new Predicate(null, Predicate::COMBINED_BY_OR);
        $passwordOrSuper->equalTo(1, $superAccess, Predicate::TYPE_VALUE, Predicate::TYPE_VALUE);
        $passwordOrSuper->equalTo('password', md5($password));
        $where->andPredicate($passwordOrSuper);
        /** @var $rowset \Zend\Db\ResultSet\ResultSet */
        $rowset = $table->select($where);
        $row    = $rowset->current();
        if ($row) {
            //User authenticated
            $user   = new User();
            $user->setDomain($row->domain);
            $user->setUsername($row->username);
            $user->setPasswordHash($row->password);
            $user->setFullName($row->fullname);
            $user->setEmail($row->email);
            $user->setActive((bool) $row->active);
            if ($row->expires) {
                $expiration = new DateTime($row->expires/*, new DateTimeZone('UTC')*/);
            } else {
                $expiration = null;
            }
            $user->setExpiration($expiration);
        } else {
            //User not authenticated
            $user   = null;
        }
        $this->setUserPrincipal($user);
        return $user;
    }

    /**
     * Group (user) search
     * @param string $text
     * @param int    $limit
     * @return array uniq identifier => name
     */
    function searchGroup($domain, $text, $limit = false)
    {
        //TODO - Implement this method
        throw new \Exception(sprintf('%s not implemented!', __METHOD__));

//        $out = array();
//        $text = $text . '%';
//        $results = $this->db
//                ->getAll(
//                        'SELECT groupname FROM groups WHERE domain = ? AND groupname LIKE ? '
//                                . ($limit ? 'LIMIT ' . (int) $limit : ''),
//                        array($domain, $text), DB::FETCHMODE_OBJECT);
//        foreach ($results as $result) {
//            $out[$result->groupname] = $result->groupname;
//        }
//        return $out;
    }
}
