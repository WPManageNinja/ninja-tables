<?php

namespace NinjaTables\Framework\Http\Request;

use NinjaTables\Framework\Support\Str;

/**
 * Class NinjaTables\Framework\Framework\Http\Request\WPUserProxy
 * 
 * @method void __construct() Constructs the class
 * @method void init() Initializes the object
 * @method mixed getDataBy(string $field, mixed $value) Retrieves data by field
 * @method bool __isset(string $name) Checks if a property is set
 * @method mixed __get(string $name) Dynamically gets a property
 * @method void __set(string $name, mixed $value) Dynamically sets a property
 * @method void __unset(string $name) Unsets a property
 * @method bool exists() Checks existence of something
 * @method mixed get(string $key) Gets a value by key
 * @method bool hasProp(string $prop) Checks if a property exists
 * @method array getRoleCaps() Gets role capabilities
 * @method void addRole(string $role, string $displayName, array $caps) Adds a role
 * @method void removeRole(string $role) Removes a role
 * @method void setRole(string $role) Sets a role
 * @method void levelReduction() Reduces levels
 * @method void updateUserLevelFromCaps() Updates user level from capabilities
 * @method void addCap(string $cap) Adds a capability
 * @method void removeCap(string $cap) Removes a capability
 * @method void removeAllCaps() Removes all capabilities
 * @method bool hasCap(string $cap) Checks if the object has a capability
 * @method string translateLevelToCap(int $level) Translates a level to a capability
 * @method self forBlog(int $blogId) Switches context to a blog
 * @method self forSite(int $siteId) Switches context to a site
 * @method int getSiteId() Gets the site ID
 */
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
     * User Meta.
     * @var array
     */
    protected $meta = null;

    /**
     * Construct the proxy.
     * 
     * @param \WP_User $wpUser
     */
    public function __construct($wpUser)
    {
        if (is_int($wpUser)) {
            $wpUser = get_user_by('id', $wpUser);
        } elseif (is_string($wpUser) && str_contains($wpUser, '@')) {
            $wpUser = get_user_by('email', $wpUser);
        }

        if (!$wpUser instanceof \WP_User) {
            throw new \InvalidArgumentException('Invalid user');
        }

        $this->wpUser = $wpUser;
    }

    /**
     * Retrieve the ID of the current user.
     * 
     * @return int
     */
    public function id()
    {
        return $this->wpUser->ID;
    }

    /**
     * Retrieve the email of the current user.
     * 
     * @return string
     */
    public function email()
    {
        return $this->wpUser->user_email;
    }

    /**
     * Retrieve the user_login of the current user.
     * 
     * @return string
     */
    public function login()
    {
        return $this->wpUser->user_login;
    }

    /**
     * Retrieve the user_nicename of the current user.
     * 
     * @return string
     */
    public function nicename()
    {
        return $this->wpUser->user_nicename;
    }

    /**
     * Retrieve the user_status of the current user.
     * 
     * @return int
     */
    public function status()
    {
        return $this->wpUser->user_status;
    }

    /**
     * Retrieve the display_name of the current user.
     * 
     * @return string
     */
    public function displayName()
    {
        return $this->wpUser->display_name;
    }
    
    /**
     * Get the roles of the current user.
     * 
     * @return array
     */
    public function getRoles()
    {
        return $this->wpUser->roles;
    }

    /**
     * Get the permissions of the current user.
     * 
     * @return array
     */
    public function getPermissions()
    {
        $getPermissions = [];

        foreach ($this->wpUser->get_role_caps() as $permission => $value) {
            if ($value) $permissions[] = $permission;
        }

        return $permissions;
    }

    /**
     * Get the meta value(s) of the current user.
     * 
     * @return mixed
     */
    public function getMeta($metaKey = null, $default = null)
    {
        if (is_null($this->meta)) {
            foreach (get_user_meta($this->wpUser->ID) as $key => $value) {
                if ($key === 'session_tokens') continue;
                $this->meta[$key] = maybe_unserialize($value[0]);
            }
        }

        if (!$metaKey) {
            return $this->meta;
        }

        return $this->meta[$metaKey] ?: $default;
    }

    /**
     * Set the meta value of the current user.
     * 
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setMeta($key, $value)
    {
        if (is_array($value) || is_object($value)) {
            $value = maybe_serialize($value);
        }

        return update_user_meta($this->wpUser->ID, $key, $value);
    }

    /**
     * Get the underlying WP_User instance.
     * @return [type] [description]
     */
    public function toBase()
    {
        return $this->wpUser;
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
        return $this->wpUser->{$key};
    }

    /**
     * Set a property on the WP_User instance.
     * 
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->wpUser->{$key} = $value;
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
