<?php
namespace T3chn0crat\LdapConnector;

use adLDAP\adLDAP;
use App;
use Exception;
use Illuminate\Support\Collection;

class LdapService
{
    /**
     * AD attributes to get
     * For more see:
     * http://msdn.microsoft.com/en-us/library/windows/desktop/ms675090%28v=vs.85%29.aspx
     * @var array
     */
    protected $fields = [];

    /**
     * Stores connection to LDAP.
     *
     * @var adLDAP
     */
    protected $adldap;

    /**
     * Creates a new LdapUserProvider and connect to Ldap
     *
     * @param array $config
     * @throws Exception
     */
    public function __construct($config)
    {
        if (!isset($config['admin_username']) || !isset($config['admin_password'])) {
            throw new Exception('ldap config needs to have admin_username and admin_password');
        }
        if (!is_array($config['fields']) || empty($config['fields'])) {
            throw new Exception('ldap config needs to ldap fields');
        }

        $this->adldap = new adLDAP($config);
        $this->fields = $config['fields'];
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->adldap->close();
    }

    /**
     * @return array
     */
    public function getAllUsers()
    {
        return $this->adldap->user()->all();
    }

    /**
     * @return Collection
     * @throws Exception
     */
    public function getAllUsersWithInfo()
    {
        $users = $this->getAllUsers();
        $collection = new Collection([]);
        foreach($users as $user) {
            $info = $this->adldap->user()->info($user, $this->fields)[0];
            if (!isset($info['displayname'])) {
                continue;
            }
            $collection->push(new \T3chn0crat\LdapConnector\LdapUserObject(
                $info,
                $this->fields
            ));
        }

        return $collection;
    }
}