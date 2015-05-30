<?php
namespace T3chn0crat\LdapConnector;

use adLDAP\adLDAP;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderInterface;
use T3chn0crat\LdapConnector\LdapUserObject;

class LdapUserProvider implements UserProviderInterface
{
    /**
     * Stores connection to LDAP
     *
     * @var adLDAP
     */
    protected $adldap;
    /**
     * AD attributes to get
     * For more see:
     * http://msdn.microsoft.com/en-us/library/windows/desktop/ms675090%28v=vs.85%29.aspx
     *
     * @var array
     */
    protected $fields;
    /**
     * User model
     *
     * @var \Model
     */
    private $model;

    /**
     * Creates a new LdapUserProvider and connect to Ldap
     *
     * @param array $config
     * @param string $model
     * @throws Exception
     */
    public function __construct($config, $model)
    {
        //We need to know which fields to fetch
        if (!is_array($config['fields']) || empty($config['fields'])) {
            throw new Exception('ldap config needs to ldap fields');
        }

        $this->adldap = new adLDAP($config);
        $this->fields = $config['fields'];
        $this->model  = $model;
    }

    /**
     *
     */
    public function __destruct()
    {
        //Need to manually close the connection
        $this->adldap->close();
    }

    /**
     * Create LDAP User Instance
     *
     * @param $userInfo
     * @return \Model
     */
    public function createUser($userInfo)
    {
        //Create a new User model with the passed in model
        $user = new $this->model([
            'username' => $userInfo['username'],
        ]);
        //Add ldap object to it
        $user->ldap = new LdapUserObject($userInfo, $this->fields);

        return $user;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return Authenticatable
     */
    public function retrieveById($identifier)
    {
        $userInfo = $this->adldap->user()
                                 ->info($identifier, $this->fields)[0];

        $userInfo['username'][0] = $identifier;

        return $this->createUser($userInfo);
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string $token
     * @return Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        // TODO: Implement retrieveByToken() method.
    }

    /**
     * @param Authenticatable $user
     * @param string          $token
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        // TODO: Implement updateRememberToken() method.
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if ($this->adldap->authenticate($credentials['username'], $credentials['password'])) {
            $userInfo                = $this->adldap->user()
                                                    ->info($credentials['username'], $this->fields)[0];
            $userInfo['username'][0] = $credentials['username'];

            return $this->createUser($userInfo);
        }
    }

    /**
     * @param Authenticatable $user
     * @param array           $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $username = $credentials['username'];
        $password = $credentials['password'];

        return $this->adldap->authenticate($username, $password);
    }
}