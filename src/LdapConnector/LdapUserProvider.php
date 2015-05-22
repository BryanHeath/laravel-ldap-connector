<?php
namespace T3chn0crat\LdapConnector;


use adLDAP\adLDAP;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderInterface;

class LdapUserProvider implements UserProviderInterface {

    protected $elements = [
        'displayname'   => 'name',
        'givenname'     => 'first_name',
        'sn'            => 'last_name',
        'username'      => 'username',
        'mail'          => 'email',
    ];

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
     * @return void
     */
    public function __construct($config)
    {
        $this->adldap = new adLDAP($config);
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->adldap->close();
    }

    /**
     * Create LDAP User Instance
     *
     * @param $userInfo
     * @return LdapUser
     */
    public function createUser($userInfo)
    {
        $values = [];
        foreach($userInfo as $key => $value){
            if (isset($this->elements[$key])) {
                $values[$this->elements[$key]] = $value[0];
            } else if ($key == 'distinguishedname' && is_array($value)) {
                if (($pos = stripos($value[0], 'dc=')) !== false) {
                    $domain = substr($value[0], $pos + 3);
                    $domain = str_ireplace(',dc=', '.', $domain);
                    $values['domain'] = $domain;
                }
            }
            $values['ldap'][$key] = $value[0];
        }

        return new LdapUser($values);
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return Authenticatable
     */
    public function retrieveById($identifier)
    {
        $userInfo = $this->adldap->user()->info($identifier, array('*'))[0];

        $userInfo['username'][0] = $identifier;
		
		return $this->createUser($userInfo);
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param  mixed $identifier
     * @param  string $token
     * @return Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        // TODO: Implement retrieveByToken() method.
    }

    /**
     * @param Authenticatable $user
     * @param string $token
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
            $userInfo = $this->adldap->user()->info($credentials['username'], array('*'))[0];
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