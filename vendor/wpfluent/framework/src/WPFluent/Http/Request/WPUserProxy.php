<?php

namespace NinjaTables\Framework\Http\Request;

use NinjaTables\Framework\Support\Str;

class WPUserProxy
{
    /**
     * The WP_User instance.
     * @var \WP_User
     */
    protected $wpUser;

    /**
     * Methods to proxy.
     * @var array
     */
    protected $passthrough = [
	    'can' => 'has_cap',
    ];

    /**
     * Construct the proxy.
     * 
     * @param \WP_User $wpUser
     */
    public function __construct(\WP_User $wpUser)
    {
        $this->wpUser = $wpUser;
    }

    /**
     * Checks if super admin (in multi-site)
     * 
     * @return boolean
     */
    public function isSuperAdmin()
    {
    	return is_super_admin($this->wpUser->ID);
    }

    /**
     * Check if the currently logged in user is an admin.
     * 
     * @return boolean
     */
    public function isAdmin()
    {
        return in_array('administrator',  $this->roles);
    }

    /**
     * Get a property from the WP_User instance.
     * 
     * @param  string $key
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function __get($key)
    {
        if (isset($this->wpUser->$key)) {
            return $this->wpUser->$key;
        }

        throw new \OutOfBoundsException(
        	"Property {$key} does not exist on WP_User"
        );
    }

    /**
     * Set a property on the WP_User instance.
     * 
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->wpUser->$key = $value;
    }

    /**
     * Checks if a property exists on the WP_User instance.
     * 
     * @param  string  $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->wpUser->$key);
    }

    /**
     * Unsets a property from the WP_User instance.
     * 
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->wpUser->$key);
    }

    /**
     * Handles method calls on the WP_User instance.
     * 
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
    	if (!method_exists($this->wpUser, $method)) {
    		if (array_key_exists($method, $this->passthrough)) {
	            $method = $this->passthrough[$method];
	        } else {
	        	$method = Str::snake($method);
	        }
        }

        if (method_exists($this->wpUser, $method)) {
            return $this->wpUser->{$method}(...$args);
        }

        throw new \BadMethodCallException(
        	"Method {$method} does not exist on WP_User"
        );
    }
}
