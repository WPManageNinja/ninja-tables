<?php

namespace NinjaTables\Framework\Http\Request;

use Closure;
use InvalidArgumentException;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Helper;
use NinjaTables\Framework\Support\MacroableTrait;
use NinjaTables\Framework\Foundation\Application;
use NinjaTables\Framework\Validator\ValidationException;

class Request
{
    use Cleaner,
        FileHandler,
        InputHelperMethodsTrait,
        MacroableTrait {
            __call as macroCall;
        }

    /**
     * The application instance
     * @var \NinjaTables\Framework\Foundation\Application
     */
    protected $app = null;

    /**
     * PHP header variables
     * @var array
     */
    protected $headers = [];

    /**
     * PHP server variables
     * @var array
     */
    protected $server = [];

    /**
     * PHP cookie variables
     * @var array
     */
    protected $cookie = [];

    /**
     * The JSON payload of the request
     * @var array
     */
    protected $json = [];

    /**
     * PHP $_GET Superglobal
     * @var array
     */
    protected $get = [];


    /**
     * PHP $_POST Superglobal
     * @var array
     */
    protected $post = [];

    /**
     * PHP $_FILES Superglobal
     * @var array
     */
    protected $files = [];

    /**
     * PHP $_GET and $_POST Superglobals
     * @var array
     */
    protected $request = [];

    /**
     * WP_REST_Request instance
     * @var WP_REST_Request
     */
    protected $wpRestRequest = false;

    /**
     * Validated data after validation has been passed
     * @var array
     */
    protected $validated = [];

    /**
     * $safe Determines the input source when data retrieval methods get called.
     * If true, the data will be returned from the $validated array.
     * If false, the data will be returned from the $request array.
     * 
     * @var boolean
     */
    protected $safe = false;

    /**
     * Construct the request instance
     * @param \NinjaTables\Framework\Foundation\Application $app
     * @param array/$_GET $get
     * @param array/$_POST $post
     * @param array/$_FILES $files
     */
    public function __construct(Application $app, $get, $post, $files)
    {
        $this->app = $app;
        $this->server = $_SERVER;
        $this->cookie = $_COOKIE;
        $this->files = $this->prepareFiles($files);

        $this->request = array_merge(
            $this->get = $this->clean($get),
            $this->post = $this->clean($post)
        );
    }

    /**
     * Variable exists
     * @param  string $key
     * @return bool
     */
    public function exists($key)
    {
        return Arr::has($this->inputs(), $key);
    }

    /**
     * Variable exists and has truthy value
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->exists($key) && !empty(Arr::get($this->inputs(), $key));
    }

    /**
     * Any variable exists and has truthy value
     * @param  string $key
     * @return bool
     */
    public function hasAny($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        if ($data = $this->only($keys)) {
            return (bool) count(array_filter($data));
        }

        return false;
    }

    /**
     * Calls a callback if has value, otherwise
     *  calls another/second callback if given.
     * 
     * @param  string $key
     * @param  \Closure $has
     * @param  \Closure|null $hasnot
     * @return mixed
     */
    public function whenHas($key, Closure $has, ?Closure $hasnot = null)
    {
        if ($this->has($key)) {
            return $has($key, $this->get($key));
        }

        return ($hasnot ? $hasnot($key) : null);
    }

    /**
     * Checks if a key is missing in the request.
     * 
     * @param  string $key
     * @return bool
     */
    public function missing($key)
    {
        return !$this->has($key);
    }

    /**
     * Calls the given callback if the provided key is missing.
     * 
     * @param  string $key
     * @param  \Closure $callback
     * @return mixed
     */
    public function whenMissing($key, Closure $callback)
    {
        if ($this->missing($key)) {
            return $callback($key, $this);
        }

        return $this;
    }

    /**
     * Set an item into the request inputs
     * @param string $key
     * @param mixed
     */
    public function set($key, $value)
    {
        Arr::set($this->request, $key, $value);

        return $this;
    }

    /**
     * Retrive all the items from the request inputs
     * @return array
     */
    public function all()
    {
        return $this->get();
    }

    /**
     * Retrieve an item from the request inputs
     * @param  string|null $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return Helper::dataGet($this->inputs(), $key, $default);
    }

    /**
     * Check the content-type for JSON
     * 
     * @return boolean
     */
    public function isJson()
    {
        return $this->is_json_content_type();
    }

    /**
     * Check if current request is a Rest request
     * 
     * @return boolean
     */
    public function isRest()
    {
        if ($this->app->isUnitTesting()) {
            return false;
        }

        $isRest = false;

        $url = $this->url();

        $niddle = $this->app->config->get(
            'app.rest_namespace'
        ).'/__endpoints';

        if (str_contains($url, $niddle)) {
            return $isRest;
        }

        $isRest = defined('REST_REQUEST') && REST_REQUEST;

        if (!$isRest) {
            if (!get_option('permalink_structure')) {
                $isRest = $this->query('rest_route', false);
            } else {
                $parsed = parse_url($url);
                $path = isset($parsed['path']) ? $parsed['path'] : '';
                $isRest = str_starts_with($path, '/wp-json');
            }
        }

        return $isRest;
    }

    /**
     * Retrieve an item from the json payload of the request
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function json($key = null, $default = null)
    {
        if (!$this->isJson()) return;
        
        if (!isset($this->json)) {
            $json = $this->get_json_params() ?: $this->getContent();
            
            $this->json = (array) json_decode($json, true);
        }

        if (is_null($key)) {
            return $this->json;
        }

        return Helper::dataGet($this->json, $key, $default);
    }

    /**
     * Retrieve an item from the PHP $_SERVER array
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function server($key = null, $default = null)
    {
        return $key ? Arr::get($this->server, $key, $default) : $this->server;
    }

    /**
     * Retrieve an item from the PHP headers
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function header($key = null, $default = null)
    {
        if (!$this->headers) {
            $this->headers = $this->setHeaders();
        }

        return $key ? Arr::get($this->headers, $key, $default) : $this->headers;
    }

    /**
     * Retrieve an item from the cookie
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function cookie($key = null, $default = null)
    {
        $cookie = $key ? Arr::get($this->cookie, $key, $default) : $this->cookie;

        return json_decode(base64_decode($cookie, true));
    }

    /**
     * Get the files array from the request.
     *
     * @return array
     */
    public function files($asCollection = false)
    {
        return $asCollection ? Helper::collect(
            $this->files
        ) : $this->files;
    }

    /**
     * Get the files as collection from the request.
     *
     * @return array
     */
    public function fileCollection()
    {
        return $this->files(true);
    }

    /**
     * Get the file from the request by key name.
     *
     * @return array
     */
    public function file($name)
    {
        if (isset($this->files[$name])) {
            return $this->files[$name];
        }

        throw new InvalidArgumentException('File does not exist: '.$name);
    }

    /**
     * Get an item from the PHP $_GET array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function query($key = null, $default = null)
    {
        return $key ? Arr::get($this->get, $key, $default) : $this->get;
    }

    /**
     * Get an item from the PHP $_POST array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        return $key ? Arr::get($this->post, $key, $default) : $this->post;
    }

    /**
     * Return the only items given in the args
     * @param  array $keys
     * @return array
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return Arr::only($this->inputs(), $keys);
    }

    /**
     * Return a subset of the request inputs except the given keys
     * @param  array $keys
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        
        return Arr::except($this->inputs(), $keys);
    }

    /**
     * Merge array with the request inputs
     * @param  array  $data
     * @return self
     */
    public function merge(array $data = [])
    {
        $this->request = array_replace($this->inputs(), $data);

        return $this;
    }

    /**
     * Merge array with the request inputs
     * @param  array  $data
     * @return self
     */
    public function mergeMissing(array $data = [])
    {
        $all = $this->inputs();

        $this->merge(Arr::mergeMissing($data, $all));

        return $this;
    }

    /**
     * Returns the request body content.
     *
     * @param bool $asResource If true, a resource will be returned
     *
     * @return string|resource
     */
    public function getContent()
    {
        if (null === $this->content || false === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    public function mergeInputsFromRestRequest($wpRestRequest)
    {
        $this->request = array_merge(
            $this->request, $wpRestRequest->get_params()
        );
        
        $this->post = array_merge(
            $this->post, $wpRestRequest->get_body_params()
        );

        $this->get = array_merge(
            $this->get, $wpRestRequest->get_query_params()
        );

        $this->wpRestRequest = true;
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        return Arr::get($this->inputs(), $key, $default);
    }

    /**
     * Remove a key(s) from the $request array
     * @param  mixed $key
     * @return self
     */
    public function forget($key)
    {
        Arr::forget($this->request, $key);

        return $this;
    }

    /**
     * Get all inputs
     * 
     * @return array $this->request
     */
    protected function inputs()
    {
        if (!$this->wpRestRequest) {
            if ($this->app->bound('wprestrequest')) {
                $this->mergeInputsFromRestRequest($this->app->wprestrequest);
            }
        }

        if ($this->safe === true) {
            $this->safe = false;
            return $this->validated;
        }

        return $this->request;
    }

    /**
     * To get item(s) from validated inputs
     *
     * @return self
     */
    public function safe()
    {
        $this->safe = true;

        return $this;
    }

    /**
     * Get user ip address
     * @return string
     */
    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $this->server('HTTP_CLIENT_IP');
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $this->server('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $this->server('REMOTE_ADDR');
        }

        return $ip;
    }

    /**
     * Taken and modified from Symfony
     */
    public function setHeaders()
    {
        $headers = array();
        $parameters = $this->server;
        $contentHeaders = array('CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true);
        foreach ($parameters as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } // CONTENT_* are not prefixed with HTTP_
            elseif (isset($contentHeaders[$key])) {
                $headers[$key] = $value;
            }
        }

        if (isset($parameters['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $parameters['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW'] = isset($parameters['PHP_AUTH_PW']) ? $parameters['PHP_AUTH_PW'] : '';
        } else {
            /*
             * php-cgi under Apache does not pass HTTP Basic user/pass to PHP by default
             * For this workaround to work, add these lines to your .htaccess file:
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             *
             * A sample .htaccess file:
             * RewriteEngine On
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             * RewriteCond %{REQUEST_FILENAME} !-f
             * RewriteRule ^(.*)$ app.php [QSA,L]
             */

            $authorizationHeader = null;
            if (isset($parameters['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $parameters['HTTP_AUTHORIZATION'];
            } elseif (isset($parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $parameters['REDIRECT_HTTP_AUTHORIZATION'];
            }

            if (null !== $authorizationHeader) {
                if (0 === stripos($authorizationHeader, 'basic ')) {
                    // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
                    $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)), 2);
                    if (count($exploded) == 2) {
                        list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                    }
                } elseif (empty($parameters['PHP_AUTH_DIGEST']) && (0 === stripos($authorizationHeader, 'digest '))) {
                    // In some circumstances PHP_AUTH_DIGEST needs to be set
                    $headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
                    $parameters['PHP_AUTH_DIGEST'] = $authorizationHeader;
                } elseif (0 === stripos($authorizationHeader, 'bearer ')) {
                    /*
                     * XXX: Since there is no PHP_AUTH_BEARER in PHP predefined variables,
                     *      I'll just set $headers['AUTHORIZATION'] here.
                     *      http://php.net/manual/en/reserved.variables.server.php
                     */
                    $headers['AUTHORIZATION'] = $authorizationHeader;
                }
            }
        }

        if (isset($headers['AUTHORIZATION'])) {
            return $headers;
        }

        // PHP_AUTH_USER/PHP_AUTH_PW
        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['AUTHORIZATION'] = 'Basic '.base64_encode($headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW']);
        } elseif (isset($headers['PHP_AUTH_DIGEST'])) {
            $headers['AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
        }

        return $headers;
    }

    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url()
    {
        return preg_replace('/\?.*/', '', $this->getFullUrl());
    }

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function getFullUrl()
    {
        return get_site_url() . rtrim($_SERVER['REQUEST_URI'], '/');
    }

    /**
     * Validate the request.
     *
     * @param  string $key
     * @return mixed
     */
    public function validate(array $rules, array $messages = [])
    {
        $instance = $this->app->make('validator');

        $validator = $instance->make($data = $this->all(), $rules, $messages);

        if ($validator->validate()->fails()) {
            throw new ValidationException(
                'Unprocessable Entity!', 422, null, $validator->errors()
            );
        }

        $this->validated = $validator->validated();

        return $data;
    }

    /**
     * Get the valid data after validation has been passed.
     *
     * @return array
     */
    public function validated($data = [])
    {
        if ($data) {
            return $this->validated = $data;
        }

        return (array) $this->validated;
    }

    /**
     * Abort the request.
     * 
     * @param  integer $status
     * @param  string  $message
     * @return \WP_REST_Response
     */
    public function abort($status = 403, $message = null)
    {
        if (is_object($status)) {
            if (method_exists($status, 'errors')) {
                throw new ValidationException(
                    'Unprocessable Entity!', 422, null, $status->errors()
                );
            }
        }

        if (!$message && !is_numeric($status) && is_string($status)) {
            $message = $status;
            $status = 403;
        }

        $message = $message ?: 'Request has benn aborted.';

        return new \WP_REST_Response(
            is_array($message) ? $message : ['message' => (string) $message], $status
        );
    }

    /**
     * Get an input element from the request.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Retrieves the currently logged in user.
     * 
     * @return \NinjaTables\Framework\Http\Request\WPUserProxy
     */
    public function user()
    {
        return new WPUserProxy(
            new \WP_User(get_current_user_id())
        );
    }

    /**
     * Dynamyc method calls (specially for WP_rest_request)
     * @param  string $method
     * @param  array $params
     * @return mixed
     */
    public function __call($method, $params = [])
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $params);
        }

        if ($method == 'route') {
            if ($params) {
                return $this->app->route->{$params[0]};
            }
            return $this->app->route;
        }
        
        if ($this->app->bound('wprestrequest')) {
            if (!method_exists($this->app->wprestrequest, $method)) {
                $method = strtolower(
                    preg_replace([
                        '/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'
                    ], '$1_$2', $method)
                );
            }

            return call_user_func_array([
                $this->app->wprestrequest, $method], $params
            );
        }
    }
}
