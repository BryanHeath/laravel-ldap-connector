![travis build](https://travis-ci.org/T3chn0crat/laravel-ldap-connector.svg?branch=master)

# Package
This package is a fork of dsdevbe's package (https://github.com/dsdevbe/ldap-connector).

This package will allow you to authenticate to and fetch data from LDAP using Laravel 5.0.x.

It uses [adLDAP library](https://github.com/adldap/adLDAP) to create a bridge between Laravel and LDAP.  adLDAP requires PHP 5 and both the [LDAP](http://php.net/ldap) and [SSL](http://php.net/openssl) libraries

## Installation
1. Install this package through Composer for Laravel v5.0:
    ```js
    composer require T3chn0crat/laravel-ldap-connector:dev-master
    ```

1. Change the authentication driver in the Laravel config to use the ldap driver. You can find this in the following file `config/auth.php`

    ```php
    'driver' => 'ldap',
    ```

1. The `config/auth.php` must also have a valid model set.  That model must include

    ```php
    public function getAuthIdentifier()
    {
        if (isset($this->ldap)) {
            return $this->ldap->samaccountname;
        }
    }
    ```

1. Create a new configuration file `ldap.php` in the configuration folder of Laravel `app/config/ldap.php` and modify to your needs. For more detail of the configuration you can always check on [adLDAP documentation](http://adldap.sourceforge.net/wiki/doku.php?id=documentation_configuration)

  **All of these are required**
    
```php
return [
	'account_suffix'=>  "@domain.local",
    // Load balancing domain controllers, but only one is requried
	'domain_controllers'=>  [
        "192.168.0.1", 
        "dc02.domain.local"
    ],
	'base_dn'   =>  'DC=domain,DC=local',
    // AD attributes to get http://msdn.microsoft.com/en-us/library/windows/desktop/ms675090%28v=vs.85%29.aspx
    'fields' => [
        'company',
        'department',
        'displayname',
        'homephone',
        'mail',
        'memberof',
        'mobile',
        'primarygroupid',
        'samaccountname',
        'telephonenumber',
        'title',
    ]
];
```

5. Once this is done you arrived at the final step and you will need to add a service provider. Open `config/app.php`, and add a new item to the providers array.

	```php
	'T3chn0crat\LdapConnector\LdapConnectorServiceProvider'
	```

## Usage

### Authentication
The LDAP plugin is an extension of the AUTH class and will act the same as normal usage with Eloquent driver.


```php
if (Auth::attempt(array('username' => $email, 'password' => $password)))
{
    return Redirect::intended('dashboard');
}
```
### Getting LDAP fields

All the fields are stored in the `Auth::user()->ldap` object as public properties.

```php
Email: {{ Auth::user()->ldap->mail }}
Department {{ Auth::user()->ldap->department }}
```

### Getting all users
You can use the LdapService object and getAllUsersWithInfo to return a Laravel Collection of LdapUserObjects.
```php
$ldap = App::make('T3chn0crat\LdapConnector\LdapService', [Config::get('ldap')]);
$collection = $ldap->getAllUsersWithInfo();
```
You can now apply all the collection function to it.  The results will be LdapUserObjects
```php
$test = $collection->where('mail', 'test@foo.com');
$department = $test->department;

```

You can find more examples on [Laravel Auth Documentation](http://laravel.com/docs/master/authentication) on using the `Auth::` function.

